using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Modules.CommonReceiveView.Domain.Model;
using GLOW.Scenes.Mission.Domain.Extension;
using GLOW.Scenes.Mission.Domain.Model;
using Zenject;

namespace GLOW.Scenes.Mission.Domain.UseCase
{
    public class ShowBonusPointMissionRewardReceivingUseCase
    {
        [Inject] IMissionCacheRepository MissionCacheRepository { get; }
        [Inject] IMstMissionDataRepository MstMissionDataRepository { get; }

        public ReceivedBonusPointMissionRewardInfoModel GetReceivedBonusPointMissionRewardInfo(MissionType missionType)
        {
            if (!missionType.ExistBonusPointMission())
            {
                return ReceivedBonusPointMissionRewardInfoModel.Empty;
            }

            var cacheModel = MissionCacheRepository.GetReceivedBonusPointMissionRewards();
            if (cacheModel.IsEmpty())
            {
                return ReceivedBonusPointMissionRewardInfoModel.Empty;
            }

            foreach (var id in cacheModel.ReceivedBonusPointRewardIds)
            {
                // 副作用
                MissionCacheRepository.UpdateMissionStatus(missionType, id, MissionStatus.Received);
            }

            var beforeBonusPoint = cacheModel.BeforeMissionBonusPointModel.Point;
            var updatedBonusPoint = cacheModel.UpdatedMissionBonusPointModel.Point;

            // 差集合を計算し、新しく獲得した宝箱ミッションのポイントを取得
            var receivedRewardBonusPoints = cacheModel.UpdatedMissionBonusPointModel.ReceivedRewardPoints
                .Except(cacheModel.BeforeMissionBonusPointModel.ReceivedRewardPoints)
                .ToList();

            var maxBonusPoint = MaxBonusPoint(missionType);


            return new ReceivedBonusPointMissionRewardInfoModel(
                beforeBonusPoint,
                updatedBonusPoint,
                maxBonusPoint,
                cacheModel.ReceivedBonusPointMissionRewards,
                receivedRewardBonusPoints);
        }

        BonusPoint MaxBonusPoint(MissionType missionType)
        {
            switch (missionType)
            {
                case MissionType.Daily:
                    return MstMissionDataRepository.GetMstMissionDailyModels()
                        .Where(model => model.CriterionType == MissionCriterionType.MissionBonusPoint)
                        .Max(model => model.CriterionCount.ToBonusPoint());
                case MissionType.Weekly:
                    return MstMissionDataRepository.GetMstMissionWeeklyModels()
                        .Where(model => model.CriterionType == MissionCriterionType.MissionBonusPoint)
                        .Max(model => model.CriterionCount.ToBonusPoint());
                case MissionType.Beginner:
                    return MstMissionDataRepository.GetMstMissionBeginnerModels()
                        .Where(model => model.CriterionType == MissionCriterionType.MissionBonusPoint)
                        .Max(model => model.CriterionCount.ToBonusPoint());
                default:
                    return BonusPoint.Empty;
            }
        }
    }
}
