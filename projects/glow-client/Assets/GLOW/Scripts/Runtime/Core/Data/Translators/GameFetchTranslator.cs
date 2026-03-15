using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Data.Data;
using GLOW.Core.Data.Translators.AdventBattle;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using WPFramework.Constants.Platform;

namespace GLOW.Core.Data.Translators
{
    public static class GameFetchTranslator
    {
        public static GameFetchModel TranslateToModel(GameFetchData gameFetchData)
        {
            var userParameterData = gameFetchData.UsrParameter;
            var userParameterModel = UserParameterTranslator.ToUserParameterModel(userParameterData);

            var stageModels = gameFetchData.UsrStages.Select(data =>
            {
                var status = new StageReleaseStatus(GetStageStatus(data));
                var clearTimeMs = data.ClearTimeMs.HasValue ? new EventClearTimeMs(data.ClearTimeMs.Value) : EventClearTimeMs.Empty;
                return new StageModel(
                    new UserDataId(data.UsrUserId),
                    new MasterDataId(data.MstStageId),
                    status,
                    clearTimeMs,
                    new StageClearCount(data.ClearCount));
            }).ToList();

            var stageEventModels = gameFetchData.UsrStageEvents.Select(data =>
            {
                return new UserStageEventModel(
                    new MasterDataId(data.MstStageId),
                    new StageClearCount(data.TotalClearCount),
                    new StageClearCount(data.ResetClearCount),
                    new StageClearCount(data.ResetAdChallengeCount),
                    data.LatestResetAt,
                    data.LastChallengedAt,
                    data.ClearTimeMs.HasValue ? new EventClearTimeMs(data.ClearTimeMs.Value) : EventClearTimeMs.Empty,
                    data.ResetClearTimeMs.HasValue ? new EventClearTimeMs(data.ResetClearTimeMs.Value) : EventClearTimeMs.Empty,
                    data.LatestEventSettingEndAt
                );
            }).ToList();

            var stageEnhanceModels = gameFetchData.UsrStageEnhances
                ?.Select(data =>
                {
                    return new UserStageEnhanceModel(
                        new MasterDataId(data.MstStageId),
                        new EnhanceQuestChallengeCount(data.ResetChallengeCount),
                        new EnhanceQuestChallengeCount(data.ResetAdChallengeCount),
                        new EnhanceQuestScore(data.MaxScore)
                    );
                })
                .ToList() ?? new List<UserStageEnhanceModel>();

            var adventBattleModels = gameFetchData.UsrAdventBattles?
                .Select(UserAdventBattleModelTranslator.ToUserAdventBattleModel).ToList();

            var badgeData = gameFetchData.Badges;
            var badgeModel = BadgeDataTranslator.ToBadgeModel(badgeData);


            var userBuyCountModel = gameFetchData.UsrBuyCount == null
                ? UserBuyCountModel.Empty
                : new UserBuyCountModel(
                    new BuyStaminaAdCount(gameFetchData.UsrBuyCount.DailyBuyStaminaAdCount),
                    gameFetchData.UsrBuyCount.DailyBuyStaminaAdAt);

            var missionStatusData = gameFetchData.MissionStatus;
            var missionStatusModel = MissionStatusDataTranslator.ToMissionStatusModel(missionStatusData);

            return new GameFetchModel(
                userParameterModel,
                stageModels,
                stageEventModels,
                stageEnhanceModels,
                adventBattleModels,
                userBuyCountModel,
                badgeModel,
                missionStatusModel);
        }

        static StageStatus GetStageStatus(UsrStageData data)
        {
            return 1 <= data.ClearCount ? StageStatus.Released : StageStatus.UnRelease;
        }
    }
}
