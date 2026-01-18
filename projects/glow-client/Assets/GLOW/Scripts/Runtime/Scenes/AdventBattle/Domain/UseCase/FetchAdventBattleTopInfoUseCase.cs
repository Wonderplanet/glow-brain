using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Constants.AdventBattle;
using GLOW.Core.Domain.Factories;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AdventBattle;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.AdventBattle.Domain.Model;
using GLOW.Scenes.InGameSpecialRule.Domain.Evaluator;
using GLOW.Scenes.PassShop.Domain.Factory;
using Zenject;

namespace GLOW.Scenes.AdventBattle.Domain.UseCase
{
    public class FetchAdventBattleTopInfoUseCase
    {
        [Inject] IMstAdventBattleDataRepository MstAdventBattleDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IInGameSpecialRuleEvaluator InGameSpecialRuleEvaluator { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] ICampaignModelFactory CampaignModelFactory { get; }
        [Inject] IHeldAdSkipPassInfoModelFactory HeldAdSkipPassInfoModelFactory { get; }

        public AdventBattleTopUseCaseModel FetchAdventBattleTop()
        {
            var gameFetch = GameRepository.GetGameFetch();
            var mstAdventBattleModels = MstAdventBattleDataRepository.GetMstAdventBattleModels();

            // 開催時間から対象の降臨バトルのマスターデータを取得
            var mstAdventBattleModel = mstAdventBattleModels.FirstOrDefault(model =>
                CalculateTimeCalculator.IsValidTime(
                    TimeProvider.Now,
                    model.StartDateTime.Value,
                    model.EndDateTime.Value), 
                MstAdventBattleModel.Empty);
            if(mstAdventBattleModel.IsEmpty())
            {
                return AdventBattleTopUseCaseModel.Empty;
            }

            var mstAdventBattleRewardGroupModel = MstAdventBattleDataRepository
                .GetMstAdventBattleRewardGroups(mstAdventBattleModel.Id);
            var highScoreRewardGroupModels = mstAdventBattleRewardGroupModel
                .Where(group => group.RewardCategory == AdventBattleRewardCategory.MaxScore)
                .ToList();

            MstEnemyCharacterModel enemyUnitFirst = MstEnemyCharacterModel.Empty;
            MstEnemyCharacterModel enemyUnitSecond = MstEnemyCharacterModel.Empty;
            MstEnemyCharacterModel enemyUnitThird = MstEnemyCharacterModel.Empty;

            if (!mstAdventBattleModel.DisplayEnemyUnitIdFirst.IsEmpty())
            {
                enemyUnitFirst = MstEnemyCharacterDataRepository
                    .GetEnemyCharacter(mstAdventBattleModel.DisplayEnemyUnitIdFirst);
            }

            if (!mstAdventBattleModel.DisplayEnemyUnitIdSecond.IsEmpty())
            {
                enemyUnitSecond = MstEnemyCharacterDataRepository
                    .GetEnemyCharacter(mstAdventBattleModel.DisplayEnemyUnitIdSecond);
            }

            if (!mstAdventBattleModel.DisplayEnemyUnitIdThird.IsEmpty())
            {
                enemyUnitThird = MstEnemyCharacterDataRepository
                    .GetEnemyCharacter(mstAdventBattleModel.DisplayEnemyUnitIdThird);
            }

            var komaBackgroundAssetKey = mstAdventBattleModel.AdventBattleAssetKey.IsEmpty() ?
                KomaBackgroundAssetKey.Empty :
                mstAdventBattleModel.AdventBattleAssetKey
                    .ToKomaBackgroundAssetKey();

            var mstAdventBattleRankModels = MstAdventBattleDataRepository
                .GetMstAdventBattleScoreRanks(mstAdventBattleModel.Id)
                .OrderBy(model => model.RequiredLowerScore)
                .ToList();

            var userAdventBattleModel = gameFetch.UserAdventBattleModels
                .FirstOrDefault(model => model.MstAdventBattleId == mstAdventBattleModel.Id,
                    UserAdventBattleModel.Empty);

            var challengeableCount = mstAdventBattleModel.ChallengeCount;
            // キャンペーン中は挑戦回数増やす
            var campaignModel = CampaignModelFactory.CreateCampaignModel(
                MasterDataId.Empty,
                CampaignTargetType.AdventBattle,
                CampaignTargetIdType.Quest,
                Difficulty.Normal,
                CampaignType.ChallengeCount);
            if (!campaignModel.IsEmpty())
            {
                challengeableCount += campaignModel.EffectValue;
            }
            // ユーザーの挑戦回数を引く
            challengeableCount -= userAdventBattleModel.ResetChallengeCount;

            var adChallengeableCount = mstAdventBattleModel.AdChallengeCount - userAdventBattleModel.ResetAdChallengeCount;

            var challengeType = challengeableCount.IsZero()
                ? AdventBattleChallengeType.Advertisement
                : AdventBattleChallengeType.Normal;

            var highScoreRewardsBefore = CreateResultModels(
                highScoreRewardGroupModels, 
                userAdventBattleModel.HighScoreLastAnimationPlayed);
            var highScoreRewardsAfter = CreateResultModels(
                highScoreRewardGroupModels, 
                userAdventBattleModel.MaxScore);

            // 今のスコアから次のランクに必要なスコアを取得
            // 現在のスコアより要求スコアが上回っているランクの情報を取得
            // まだ次のランクが存在する場合はその中で最小のものを取得、なければ空を返す
            // var nextRankScoreThreshold = mstAdventBattleRankModels
            //     .Where(rankModel => rankModel.RequiredLowerScore > userAdventBattleModel.TotalScore)
            //     .Min(rankModel => rankModel.RequiredLowerScore) ?? AdventBattleScore.Empty;
            var currentRankModel  = MstAdventBattleScoreRankModel.Empty;
            var nextRankModel = MstAdventBattleScoreRankModel.Empty;
            foreach (var model in mstAdventBattleRankModels)
            {
                if (model.RequiredLowerScore <= userAdventBattleModel.TotalScore)
                {
                    currentRankModel = model;
                }
                else
                {
                    nextRankModel = model;
                    break;
                }
            }

            // 次のランクに必要なスコアを計算
            var needNextRankScore =
                nextRankModel.IsEmpty() ?
                    AdventBattleScore.Empty :
                    nextRankModel.RequiredLowerScore - userAdventBattleModel.TotalScore;

            // 降臨バトルのみんなでの協力スコアを取得
            var raidTotalScores = GetRaidTotalScores(
                mstAdventBattleModel.BattleType, 
                mstAdventBattleModel.Id);

            // 現在選択されているパーティのパーティ名を取得
            var partyName = PartyCacheRepository.GetCurrentPartyModel().PartyName;

            // 終了までの残り時間の計算
            var remainingTime = CalculateTimeCalculator.GetRemainingTime(
                TimeProvider.Now, 
                mstAdventBattleModel.EndDateTime.Value);

            var adventBattleMissionBadge = new NotificationBadge(
                !gameFetch.BadgeModel.UnreceivedMissionAdventBattleRewardCount.IsZero());

            // ランキングが集計中かどうか
            var rankingUpdateMinutes = MstConfigRepository
                .GetConfig(MstConfigKey.AdventBattleRankingUpdateIntervalMinutes).Value.ToInt();
            var firstRankingUpdateTime = mstAdventBattleModel.StartDateTime.AddMinutes(rankingUpdateMinutes);

            var heldAdSkipPassInfoModel = HeldAdSkipPassInfoModelFactory.CreateHeldAdSkipPassInfo();
            var campaignModels = CampaignModelFactory.CreateCampaignModels(
                MasterDataId.Empty,
                CampaignTargetType.AdventBattle,
                CampaignTargetIdType.Quest,
                Difficulty.Normal);

            return new AdventBattleTopUseCaseModel(
                mstAdventBattleModel.Id,
                mstAdventBattleModel.BattleType,
                mstAdventBattleModel.EventBonusGroupId,
                challengeableCount,
                adChallengeableCount,
                challengeType,
                userAdventBattleModel.TotalScore,
                needNextRankScore,
                userAdventBattleModel.MaxScore,
                userAdventBattleModel.HighScoreLastAnimationPlayed,
                raidTotalScores.currentRaidTotalScore,
                raidTotalScores.nextRewardRequiredRaidTotalScore,
                currentRankModel.RankType,
                currentRankModel.ScoreRankLevel,
                enemyUnitFirst.AssetKey.IsEmpty() ?
                    UnitImageAssetPath.Empty :
                    UnitImageAssetPath.FromAssetKey(enemyUnitFirst.AssetKey),
                enemyUnitSecond.AssetKey.IsEmpty() ?
                    UnitImageAssetPath.Empty :
                    UnitImageAssetPath.FromAssetKey(enemyUnitSecond.AssetKey),
                enemyUnitThird.AssetKey.IsEmpty() ?
                    UnitImageAssetPath.Empty :
                    UnitImageAssetPath.FromAssetKey(enemyUnitThird.AssetKey),
                komaBackgroundAssetKey.IsEmpty() ?
                    KomaBackgroundAssetPath.Empty :
                    KomaBackgroundAssetPath.FromAssetKey(komaBackgroundAssetKey),
                highScoreRewardsBefore,
                highScoreRewardsAfter,
                remainingTime,
                partyName,
                InGameSpecialRuleEvaluator.ExistsSpecialRule(
                    InGameContentType.AdventBattle, 
                    mstAdventBattleModel.Id, 
                    QuestType.Event),
                adventBattleMissionBadge,
                new AdventBattleFirstRankingUpdateDateTime(firstRankingUpdateTime.Value),
                mstAdventBattleModel.AdventBattleName,
                mstAdventBattleModel.AdventBattleBossDescription,
                heldAdSkipPassInfoModel,
                campaignModels
            );
        }

        IReadOnlyList<AdventBattleHighScoreRewardModel> CreateResultModels(
            IReadOnlyList<MstAdventBattleRewardGroupModel> mstAdventBattleRewardGroupModels,
            AdventBattleScore beforeMaxScore)
        {
            var pickReward = mstAdventBattleRewardGroupModels.Last();
            return mstAdventBattleRewardGroupModels
                .Select(group => CreateAdventBattleHighScoreRewardModel(beforeMaxScore, group, group == pickReward))
                .ToList();
        }

        AdventBattleHighScoreRewardModel CreateAdventBattleHighScoreRewardModel(
            AdventBattleScore beforeMaxScore,
            MstAdventBattleRewardGroupModel model,
            bool isPickup)
        {
            var isObtained = new AdventBattleHighScoreRewardObtainedFlag(
                beforeMaxScore >= model.RewardCondition.ToAdventBattleScore());

            var reward = model.Rewards
                .Select(CreatePlayerResourceModel)
                .FirstOrDefault(PlayerResourceModel.Empty);

            return new AdventBattleHighScoreRewardModel(
                model.RewardCondition.ToAdventBattleScore(),
                reward,
                isObtained,
                new AdventBattleHighScoreRewardPickupFlag(isPickup));
        }

        PlayerResourceModel CreatePlayerResourceModel(MstAdventBattleRewardModel rewardModel)
        {
            return PlayerResourceModelFactory.Create(
                rewardModel.ResourceType,
                rewardModel.ResourceId,
                rewardModel.ResourceAmount.ToPlayerResourceAmount());
        }

        (AdventBattleRaidTotalScore currentRaidTotalScore, AdventBattleRaidTotalScore nextRewardRequiredRaidTotalScore) GetRaidTotalScores(
            AdventBattleType battleType,
            MasterDataId mstAdventBattleId)
        {
            if (battleType == AdventBattleType.ScoreChallenge)
            {
                return (AdventBattleRaidTotalScore.Empty, AdventBattleRaidTotalScore.Empty);
            }

            var gameFetchOther = GameRepository.GetGameFetchOther();
            var raidTotalScoreModel = gameFetchOther.AdventBattleRaidTotalScoreModel;
            if (raidTotalScoreModel.MstAdventBattleId != mstAdventBattleId)
            {
                return (AdventBattleRaidTotalScore.Empty, AdventBattleRaidTotalScore.Empty);
            }

            // 降臨バトルのみんなでの協力スコアを取得、次の報酬までのスコアを計算
            var currentRaidTotalScore = raidTotalScoreModel.AdventBattleRaidTotalScore;
            var mstAdventBattleRewardGroupModel = MstAdventBattleDataRepository.GetMstAdventBattleRewardGroups(mstAdventBattleId);
            var raidTotalScoreRewardModels = mstAdventBattleRewardGroupModel
                .Where(group => group.RewardCategory == AdventBattleRewardCategory.RaidTotalScore).ToList();
            var nextRewardRequiredRaidTotalScoreModel = raidTotalScoreRewardModels.MinByAboveLowerLimit(
                model => model.RewardCondition.ToAdventBattleRaidTotalScore().Value,
                currentRaidTotalScore.Value) ?? MstAdventBattleRewardGroupModel.Empty;

            var nextRewardRequiredRaidTotalScore = nextRewardRequiredRaidTotalScoreModel.IsEmpty() ?
                AdventBattleRaidTotalScore.Empty :
                nextRewardRequiredRaidTotalScoreModel.RewardCondition.ToAdventBattleRaidTotalScore() - currentRaidTotalScore;

            return (currentRaidTotalScore, nextRewardRequiredRaidTotalScore);
        }
    }
}
