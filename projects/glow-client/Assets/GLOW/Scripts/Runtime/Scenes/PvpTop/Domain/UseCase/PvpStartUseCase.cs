using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.UseCases;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.AdventBattle.Domain.Factory;
using GLOW.Scenes.AdventBattleRanking.Domain.ModelFactories;
using GLOW.Scenes.InGame.Domain.Models.LogModel;
using GLOW.Scenes.PvpTop.Domain.Model;
using GLOW.Scenes.PvpTop.Domain.Resolver;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.PvpTop.Domain.UseCase
{
    public class PvpStartUseCase
    {
        [Inject] IPvpService PvpService { get; }
        [Inject] IPvpStartModelFactory PvpStartModelFactory { get; }
        [Inject] IPartyStatusModelFactory PartyStatusModelFactory { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IPvpSelectedOpponentStatusCacheRepository PvpSelectedOpponentStatusCacheRepository { get; }
        [Inject] IResumableStateRepository ResumableStateRepository { get; }
        [Inject] ISelectedStageRepository SelectedStageRepository { get; }
        [Inject] IPvpTopCacheRepository PvpTopCacheRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IInGamePreferenceRepository InGamePreferenceRepository { get; }
        [Inject] IMstInGameSpecialRuleDataRepository MstInGameSpecialRuleDataRepository { get; }
        [Inject] IMstInGameSpecialRuleUnitStatusDataRepository MstInGameSpecialRuleUnitStatusDataRepository { get; }
        [Inject] IMstCurrentPvpModelResolver MstCurrentPvpModelResolver { get; }

        public async UniTask<PvpStartUseCaseModel> StartPvp(
            UserMyId opponentMyId,
            PvpChallengeType challengeType,
            CancellationToken cancellationToken)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var sysPvpSeasonModel = gameFetchOther.SysPvpSeasonModel;
            var currentPartyModel = PartyCacheRepository.GetCurrentPartyModel();
            var mstPvpModel = MstCurrentPvpModelResolver.CreateMstPvpModel(sysPvpSeasonModel.Id);
            var specialRuleModels = MstInGameSpecialRuleDataRepository
                .GetInGameSpecialRuleModels(
                    mstPvpModel.Id,
                    InGameContentType.Pvp);
            var groupIdList = specialRuleModels
                .Where(m => m.RuleType == RuleType.UnitStatus)
                .Select(m => m.RuleValue.ToMasterDataId())
                .Distinct()
                .ToList();
            var specialRuleUnitStatusModels = MstInGameSpecialRuleUnitStatusDataRepository
                .GetInGameSpecialRuleUnitStatusModels(groupIdList);
            var partyNo = currentPartyModel.PartyNo;
            var userUnitModels = gameFetchOther.UserUnitModels;
            var partyStatusModels = currentPartyModel
                .GetUnitList()
                .Join(userUnitModels, id => id, model => model.UsrUnitId, (_, model) => model)
                .Select(m => PartyStatusModelFactory.CreatePartyStatusModel(
                    m,
                    InGameType.Pvp,
                    MasterDataId.Empty,
                    EventBonusGroupId.Empty, // Pvpではイベントボーナスは無いはず
                    specialRuleUnitStatusModels))
                .ToList();
            var inGameStartBattleLogModel = new InGameStartBattleLogModel(partyStatusModels);

            var resultModel = await PvpService.Start(
                cancellationToken,
                sysPvpSeasonModel.Id.ToString(),
                challengeType == PvpChallengeType.Ticket ? 1 : 0, // アイテムを使う場合は1, 通常の挑戦回数は0
                opponentMyId.ToString(),
                partyNo.ToInt(),
                inGameStartBattleLogModel);

            var startUseCaseModel = PvpStartModelFactory.CreatePvpStartUseCaseModel(resultModel);
            SetRepositories(sysPvpSeasonModel.Id, startUseCaseModel.OpponentPvpStatus);
            InGamePreferenceRepository.IsInGameContinueSelecting = InGameContinueSelectingFlag.False;

            return startUseCaseModel;
        }

        // 副作用
        void SetRepositories(
            ContentSeasonSystemId sysPvpSeasonId,
            OpponentPvpStatusModel opponentPvpStatusModel)
        {
            PvpTopCacheRepository.SetPvpTopApiCallAllowedStatus(new PvpTopApiCallAllowedStatus(true, TimeProvider.Now));
            SelectedStageRepository.Save(
                new SelectedStageModel(MasterDataId.Empty, MasterDataId.Empty, sysPvpSeasonId));
            ResumableStateRepository.Save(
                new ResumableStateModel(SceneViewContentCategory.Pvp, sysPvpSeasonId.ToMasterDataId(), MasterDataId.Empty));
            PvpSelectedOpponentStatusCacheRepository.SetOpponentStatus(opponentPvpStatusModel);
        }
    }
}

