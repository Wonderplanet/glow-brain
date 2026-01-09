using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Constants.Benchmark;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.AdventBattle;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.TimeMeasurement;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.AdventBattle.Domain.Definition.Service;
using GLOW.Scenes.AdventBattle.Domain.Factory;
using GLOW.Scenes.Home.Domain.Constants;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.LogModel;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Domain.UseCase
{
    public class AdventBattleStartUseCase
    {
        [Inject] IAdventBattleService AdventBattleService { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }

        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPartyStatusModelFactory PartyStatusModelFactory { get; }
        [Inject] IResumableStateRepository ResumableStateRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IStageLimitStatusModelFactory StageLimitStatusModelFactory { get; }
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] ISelectedStageRepository SelectedStageRepository { get; }
        [Inject] IInGameLoadingMeasurement InGameLoadingMeasurement { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }
        [Inject] IInGamePreferenceRepository InGamePreferenceRepository { get; }
        [Inject] IPvpSelectedOpponentStatusCacheRepository PvpSelectedOpponentStatusCacheRepository { get; }
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IMstInGameSpecialRuleUnitStatusDataRepository MstInGameSpecialRuleUnitStatusDataRepository { get; }

        public async UniTask<AdventBattleStartUseCaseResultModel> StartAdventBattle(
            CancellationToken cancellationToken,
            MasterDataId mstAdventBattleId,
            AdventBattleChallengeType challengeType)
        {
            // NOTE: ログの計測開始
            InGameLoadingMeasurement.Start();

            // 決闘終了時、期間外でエラーが起きると正しくPvpの終了処理が行われないので、決闘の相手情報の破棄を最初に明示的に行う
            PvpSelectedOpponentStatusCacheRepository.ClearOpponentStatus();

            var battleModel = MstAdventBattleDataRepository.GetMstAdventBattleModel(mstAdventBattleId);

            var userUnitModels = GameRepository.GetGameFetchOther().UserUnitModels;
            var specialRuleModels = MstInGameSpecialRuleDataRepository
                .GetInGameSpecialRuleModels(
                    mstAdventBattleId,
                    InGameContentType.AdventBattle);
            var groupIdList = specialRuleModels
                .Where(m => m.RuleType == RuleType.UnitStatus)
                .Select(m => m.RuleValue.ToMasterDataId())
                .Distinct()
                .ToList();
            var specialRuleUnitStatusModels = MstInGameSpecialRuleUnitStatusDataRepository
                .GetInGameSpecialRuleUnitStatusModels(groupIdList);
            var partyStatusModels = PartyCacheRepository
                .GetCurrentPartyModel()
                .GetUnitList()
                .Join(userUnitModels, id => id, model => model.UsrUnitId, (_, model) => model)
                .Select(m => PartyStatusModelFactory.CreatePartyStatusModel(
                    m,
                    InGameType.AdventBattle,
                    MasterDataId.Empty, // 降臨バトルではEventBonusGroupIdを使用するのでクエストIDは不要
                    battleModel.EventBonusGroupId,
                    specialRuleUnitStatusModels))
                .ToList();

            var invalidPartyModel = GetStageLimitInvalidStatusModel(mstAdventBattleId);

            // キャンペーン情報取得
            var campaignModel = CampaignModelFactory.CreateCampaignModel(
                MasterDataId.Empty,
                CampaignTargetType.AdventBattle,
                CampaignTargetIdType.Quest,
                Difficulty.Normal,
                CampaignType.ChallengeCount);
            var errorType = CheckError(challengeType, battleModel, invalidPartyModel, campaignModel);

            //エラーあったらAPI叩かずreturn
            if(errorType != AdventBattleErrorType.None)
            {
                return new AdventBattleStartUseCaseResultModel(errorType, GetInGameSpecialRuleLimitStatusModel(mstAdventBattleId));
            }

            UpdateRepositories(mstAdventBattleId);
            InGamePreferenceRepository.IsInGameContinueSelecting = InGameContinueSelectingFlag.False;

            await AdventBattleService.Start(
                cancellationToken,
                mstAdventBattleId,
                PartyCacheRepository.GetCurrentPartyModel().PartyNo,
                challengeType,
                new InGameStartBattleLogModel(partyStatusModels));

            return new AdventBattleStartUseCaseResultModel(errorType, GetInGameSpecialRuleLimitStatusModel(mstAdventBattleId));
        }

        void UpdateRepositories(MasterDataId mstAdventBattleId)
        {
            ResumableStateRepository.Save(new ResumableStateModel(SceneViewContentCategory.AdventBattle, mstAdventBattleId, MasterDataId.Empty));
            var selectedStageModel = new SelectedStageModel(MasterDataId.Empty, mstAdventBattleId, ContentSeasonSystemId.Empty);
            SelectedStageRepository.Save(selectedStageModel);
        }

        AdventBattleErrorType CheckError(
            AdventBattleChallengeType challengeType,
            MstAdventBattleModel model,
            InGameSpecialRuleStatusModel invalidPartyModel,
            CampaignModel campaignModel)
        {
            if (invalidPartyModel.LimitStatus.Any())
            {
                return AdventBattleErrorType.InvalidParty;
            }

            if(CheckOutOfTime(model.EndDateTime))
            {
                return AdventBattleErrorType.OutOfTime;
            }
            if(CheckOverChallengeCount(
                   model.Id,
                   challengeType,
                   model.ChallengeCount,
                   model.AdChallengeCount,
                   campaignModel))
            {
                return AdventBattleErrorType.OverChallengeCount;
            }

            return AdventBattleErrorType.None;
        }

        InGameSpecialRuleStatusModel GetStageLimitInvalidStatusModel(MasterDataId mstAdventBattleId)
        {
            // 選択中パーティ取得
            var currentParty = PartyCacheRepository.GetCurrentPartyModel();
            var userUnitModels = GameRepository.GetGameFetchOther().UserUnitModels;
            var calcTargetMstCharacterModels = currentParty.GetUnitList()
                .Where(c => !c.IsEmpty())
                .Select(id =>
                {
                    var userUnit = userUnitModels.Find(model => model.UsrUnitId.Value == id.Value);
                    return MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);
                })
                .ToList();

            return StageLimitStatusModelFactory.CreateInvalidStageLimitStatusModel(
                mstAdventBattleId,
                InGameContentType.AdventBattle,
                currentParty.PartyName,
                calcTargetMstCharacterModels);
        }

        InGameSpecialRuleStatusModel GetInGameSpecialRuleLimitStatusModel(MasterDataId specialRuleTargetMstId)
        {
            // 選択中パーティ取得
            var currentParty = PartyCacheRepository.GetCurrentPartyModel();

            return StageLimitStatusModelFactory.CreateStageLimitStatusModel(
                specialRuleTargetMstId,
                InGameContentType.AdventBattle,
                currentParty.PartyName);
        }

        bool CheckOutOfTime(AdventBattleEndDateTime endDateTime)
        {
            return endDateTime < TimeProvider.Now;
        }

        bool CheckOverChallengeCount(
            MasterDataId mstAdventBattleId,
            AdventBattleChallengeType challengeType,
            AdventBattleChallengeCount challengeCount,
            AdventBattleChallengeCount adChallengeCount,
            CampaignModel campaignModel)
        {
            var gameFetch = GameRepository.GetGameFetch();
            var userAdventBattleModel = gameFetch.UserAdventBattleModels
                .FirstOrDefault(model => model.MstAdventBattleId == mstAdventBattleId);
            var userAdventBattle =  userAdventBattleModel ?? UserAdventBattleModel.Empty;

            if(challengeType == AdventBattleChallengeType.Normal)
            {
                // キャンペーン中はキャンペーンの回数も加算してチェックする
                if (!campaignModel.IsEmpty() && campaignModel.IsChallengeCountCampaign())
                {
                    challengeCount += campaignModel.EffectValue;
                }
                return (challengeCount - userAdventBattle.ResetChallengeCount).IsZero();
            }
            else
            {
                return (adChallengeCount - userAdventBattle.ResetAdChallengeCount).IsZero();
            }
        }
    }
}
