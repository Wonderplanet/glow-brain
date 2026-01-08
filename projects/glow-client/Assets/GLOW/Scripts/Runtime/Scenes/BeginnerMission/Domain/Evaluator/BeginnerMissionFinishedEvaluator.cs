using System.Linq;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Mission;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Mission.Domain.Creator;
using Zenject;

namespace GLOW.Scenes.BeginnerMission.Domain.UseCase
{
    public class BeginnerMissionFinishedEvaluator : IBeginnerMissionFinishedEvaluator
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMissionCacheRepository MissionCacheRepository { get; }
        [Inject] IMstMissionDataRepository MissionDataRepository { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IMissionResultModelFactory MissionResultModelFactory { get; }

        public BeginnerMissionFinishedFlag CheckBeginnerMissionAllCompleted()
        {
            var fetchModel = GameRepository.GetGameFetch();
            var missionModel = MissionCacheRepository.GetMissionModel();
            if(missionModel == null)
            {
                // ログイン直後はキャッシュがないのでfetchModelの情報を返す
                return new BeginnerMissionFinishedFlag(fetchModel.MissionStatusModel.BeginnerMissionAllCompleted.Value);
            }

            var userMissionBeginner = missionModel.UserMissionBeginnerModels;
            var userMissionBonusPoint = missionModel.UserMissionBonusPointModels;
            var missionBeginnerResultModel = MissionResultModelFactory.CreateMissionBeginnerResultModel(MissionDataRepository, PlayerResourceModelFactory, userMissionBeginner, userMissionBonusPoint);

            // 全て受け取っているか
            var bonusPointMissionAllReceived = missionBeginnerResultModel.BonusPointResultModel
                .BonusPointCellModels.All(cell => cell.MissionStatus == MissionStatus.Received);
            var beginnerMissionCellAllCompleted = missionBeginnerResultModel
                .MissionBeginnerModel.All(cell => cell.MissionStatus == MissionStatus.Received);

            return new BeginnerMissionFinishedFlag(beginnerMissionCellAllCompleted && bonusPointMissionAllReceived);
        }

    }
}
