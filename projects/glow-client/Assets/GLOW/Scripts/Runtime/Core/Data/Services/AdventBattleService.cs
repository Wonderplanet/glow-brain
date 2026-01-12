using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Translators;
using GLOW.Core.Data.Translators.AdventBattle;
using GLOW.Core.Domain.Constants.AdventBattle;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.AdventBattle.Domain.Definition.Service;
using GLOW.Scenes.InGame.Domain.Models.LogModel;
using UnityHTTPLibrary;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class AdventBattleService : IAdventBattleService
    {
        [Inject] AdventBattleApi AdventBattleApi { get; }

        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }

        async UniTask IAdventBattleService.Start(
            CancellationToken cancellationToken,
            MasterDataId mstAdventBattleId,
            PartyNo partyNo,
            AdventBattleChallengeType challengeType,
            InGameStartBattleLogModel inGameStartBattleLogModel
            )
        {
            try
            {
                var data = InGameStartBattleLogModelTranslator.ToInGameStartBattleLogData(inGameStartBattleLogModel);
                await AdventBattleApi.Start(
                    cancellationToken,
                    mstAdventBattleId.Value,
                    partyNo.Value,
                    challengeType == AdventBattleChallengeType.Advertisement,
                    data);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<AdventBattleEndResultModel> IAdventBattleService.End(
            CancellationToken cancellationToken,
            MasterDataId mstAdventBattleId,
            InGameEndBattleLogModel inGameEndBattleLogModel)
        {
            try
            {
                var inGameBattleLogData = InGameEndBattleLogDataTranslator.ToInGameEndBattleLogData(inGameEndBattleLogModel);
                var result = await AdventBattleApi.End(cancellationToken, mstAdventBattleId.Value, inGameBattleLogData);
                return AdventBattleEndResultModelTranslator.ToAdventBattleEndResultModel(result);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<AdventBattleAbortResultModel> IAdventBattleService.Abort(
            CancellationToken cancellationToken,
            AdventBattleAbortType abortType)
        {
            try
            {
                var result = await AdventBattleApi.Abort(cancellationToken, (int)abortType);
                return AdventBattleAbortResultModelTranslator.ToAdventBattleAbortResultModel(result);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask IAdventBattleService.Cleanup(CancellationToken cancellationToken)
        {
            try
            {
                await AdventBattleApi.Cleanup(cancellationToken);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<AdventBattleTopResultModel> IAdventBattleService.Top(
            CancellationToken cancellationToken,
            MasterDataId mstAdventBattleId)
        {
            try
            {
                var result = await AdventBattleApi.Top(cancellationToken, mstAdventBattleId.Value);
                return AdventBattleTopResultModelTranslator.ToAdventBattleTopResultModel(result);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<AdventBattleRankingResultModel> IAdventBattleService.GetRanking(
            CancellationToken cancellationToken,
            MasterDataId mstAdventBattleId,
            bool isPrevious)
        {
            try
            {
                var result = await AdventBattleApi.Ranking(cancellationToken, mstAdventBattleId.Value, isPrevious);
                return AdventBattleRankingResultModelTranslator.ToAdventBattleRankingModel(result);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<AdventBattleInfoResultModel> IAdventBattleService.GetInfo(CancellationToken cancellationToken)
        {
            try
            {
                var result = await AdventBattleApi.Info(cancellationToken);
                return AdventBattleInfoResultModelTranslator.ToAdventBattleInfoResultModel(result);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }
    }
}
