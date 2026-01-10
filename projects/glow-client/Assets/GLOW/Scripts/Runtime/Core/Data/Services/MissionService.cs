using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Mission;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Mission.Domain.Definition.Service;
using UnityHTTPLibrary;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class MissionService : IMissionService
    {
        [Inject] MissionApi MissionApi { get; }

        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }

        async UniTask<MissionUpdateAndFetchResultModel> IMissionService.UpdateAndFetch(CancellationToken cancellationToken)
        {
            try
            {
                var missionUpdateAndFetchData = await MissionApi.UpdateAndFetch(cancellationToken);
                return MissionUpdateAndFetchResultDataTranslator.ToMissionUpdateAndFetchResultData(
                    missionUpdateAndFetchData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<MissionEventUpdateAndFetchResultModel> IMissionService.EventUpdateAndFetch(CancellationToken cancellationToken)
        {
            try
            {
                var missionEventUpdateAndFetchData = await MissionApi.EventUpdateAndFetch(cancellationToken);
                return MissionEventUpdateAndFetchResultDataTranslator.ToMissionEventUpdateAndFetchResultModel(
                    missionEventUpdateAndFetchData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<MissionAdventBattleFetchResultModel> IMissionService.AdventBattleUpdateAndFetch(CancellationToken cancellationToken)
        {
            try
            {
                var missionAdventBattleFetchData = await MissionApi.AdventBattleFetch(cancellationToken);
                return MissionAdventBattleFetchDataTranslator.ToMissionAdventBattleModel(missionAdventBattleFetchData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<MissionClearOnCallResultModel> IMissionService.ClearOnCall(
            CancellationToken cancellationToken,
            MissionType missionType, 
            MasterDataId missionId)
        {
            try
            {
                var clearOnCallData = await MissionApi.ClearOnCall(
                    cancellationToken,
                    missionType.ToString(),
                    missionId.ToString());
                return MissionClearOnCallResultDataTranslator.ToMissionClearOnCallResultModel(clearOnCallData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<MissionBulkReceiveRewardResultModel> IMissionService.ReceiveReward(
            CancellationToken cancellationToken,
            MissionType missionType, 
            MasterDataId missionId)
        {
            try
            {
                var receiveRewardData = await MissionApi.BulkReceiveReward(
                    cancellationToken, 
                    missionType.ToString(), 
                    new[] { missionId.ToString() });
                return MissionBulkReceiveRewardResultDataTranslator.ToMissionBulkReceiveRewardResultModel(
                    receiveRewardData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<MissionBulkReceiveRewardResultModel> IMissionService.BulkReceiveReward(
            CancellationToken cancellationToken, 
            MissionType missionType,
            IReadOnlyList<MasterDataId> missionIds)
        {
            try
            {
                var receiveRewardData = await MissionApi.BulkReceiveReward(
                    cancellationToken, 
                    missionType.ToString(), 
                    missionIds.Select(id => id.ToString()).ToArray());
                return MissionBulkReceiveRewardResultDataTranslator.ToMissionBulkReceiveRewardResultModel(
                    receiveRewardData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<MissionEventDailyBonusUpdateResultModel> IMissionService.ReceiveEventDailyBonusUpdate(CancellationToken cancellationToken)
        {
            try
            {
                var receiveRewardData = await MissionApi.EventDailyBonusUpdate(cancellationToken);
                return MissionEventDailyBonusUpdateResultModelTranslator.ToMissionEventDailyBonusUpdateResultModel(
                    receiveRewardData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }
    }
}
