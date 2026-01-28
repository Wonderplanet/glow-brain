using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Translators;
using GLOW.Core.Data.Translators.Pvp;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models.LogModel;
using UnityHTTPLibrary;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public class PvpService : IPvpService
    {
        [Inject] PvpApi PvpApi { get; }
        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }

        async UniTask<PvpTopResultModel> IPvpService.Top(CancellationToken cancellationToken)
        {
            try
            {
                var data = await PvpApi.Top(cancellationToken);
                return PvpDataTranslator.ToPvpTopResultModel(data);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<PvpStartResultModel> IPvpService.Start(
            CancellationToken cancellationToken,
            string sysPvpSeasonId,
            int isUseItem, // 通常の挑戦回数の場合は0, アイテムを使って挑戦する場合は1
            string opponentMyId,
            int partyNo,
            InGameStartBattleLogModel inGameStartBattleLogModel)
        {
            try
            {
                var inGameStartBattleLogData =
                    InGameStartBattleLogModelTranslator.ToInGameStartBattleLogData(inGameStartBattleLogModel);
                var data = await PvpApi.Start(
                    cancellationToken,
                    sysPvpSeasonId,
                    isUseItem,
                    opponentMyId,
                    partyNo,
                    inGameStartBattleLogData
                );

                return PvpDataTranslator.ToPvpStartResultModel(data);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<PvpEndResultModel> IPvpService.End(
            CancellationToken cancellationToken,
            ContentSeasonSystemId sysPvpSeasonId,
            PvpInGameEndBattleLogModel pvpInGameEndBattleLogModel,
            bool isWin)
        {
            try
            {
                var pvpInGameEndBattleLogData =
                    PvpInGameEndBattleLogDataTranslator.ToPvpInGameEndBattleLogData(pvpInGameEndBattleLogModel);
                var data = await PvpApi.End(
                    cancellationToken,
                    sysPvpSeasonId.ToString(),
                    pvpInGameEndBattleLogData,
                    isWin
                );

                return PvpDataTranslator.ToPvpEndResultModel(data);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<PvpResumeResultModel> IPvpService.Resume(CancellationToken cancellationToken)
        {
            try
            {
                var data = await PvpApi.Resume(cancellationToken);
                return PvpDataTranslator.ToPvpResumeResultModel(data);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<PvpChangeOpponentResultModel> IPvpService.ChangeOpponent(CancellationToken cancellationToken)
        {
            try
            {
                var data = await PvpApi.ChangeOpponent(cancellationToken);
                return PvpDataTranslator.ToPvpChangeOpponentResultModel(data);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<PvpAbortResultModel> IPvpService.Abort(CancellationToken cancellationToken)
        {
            try
            {
                var data = await PvpApi.Abort(cancellationToken);
                return PvpDataTranslator.ToPvpAbortResultModel(data);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask IPvpService.Cleanup(CancellationToken cancellationToken)
        {
            try
            {
                await PvpApi.Cleanup(cancellationToken);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<PvpRankingResultModel> IPvpService.Ranking(
            CancellationToken cancellationToken,
            bool isPreviousSeason)
        {
            try
            {
                var data = await PvpApi.Ranking(cancellationToken, isPreviousSeason);
                return PvpDataTranslator.ToPvpRankingResultModel(data);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }
    }
}
