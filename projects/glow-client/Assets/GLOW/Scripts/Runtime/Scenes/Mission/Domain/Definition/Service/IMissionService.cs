using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Mission.Domain.Definition.Service
{
    public interface IMissionService
    {
        UniTask<MissionUpdateAndFetchResultModel> UpdateAndFetch(CancellationToken cancellationToken);
        UniTask<MissionEventUpdateAndFetchResultModel> EventUpdateAndFetch(CancellationToken cancellationToken);
        UniTask<MissionAdventBattleFetchResultModel> AdventBattleUpdateAndFetch(CancellationToken cancellationToken);
        UniTask<MissionClearOnCallResultModel> ClearOnCall(
            CancellationToken cancellationToken,
            MissionType missionType, 
            MasterDataId missionId);
        UniTask<MissionBulkReceiveRewardResultModel> ReceiveReward(
            CancellationToken cancellationToken,
            MissionType missionType, 
            MasterDataId missionId);
        UniTask<MissionBulkReceiveRewardResultModel> BulkReceiveReward(
            CancellationToken cancellationToken, 
            MissionType missionType,
            IReadOnlyList<MasterDataId> missionIds);
        UniTask<MissionEventDailyBonusUpdateResultModel> ReceiveEventDailyBonusUpdate(CancellationToken cancellationToken);
    }
}