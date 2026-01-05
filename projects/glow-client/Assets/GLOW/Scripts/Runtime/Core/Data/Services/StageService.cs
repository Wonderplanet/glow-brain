using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Translators;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Stage;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.Models.LogModel;
using UnityHTTPLibrary;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public sealed class StageService : IStageService
    {
        [Inject] StageApi StageApi { get;}
        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }

        async UniTask<StageStartResultModel> IStageService.Start(
            CancellationToken cancellationToken,
            MasterDataId mstStageId,
            PartyNo partyNo,
            bool isChallengeAd,
            StaminaBoostCount staminaBoostCount)
        {
            try
            {
                var data = await StageApi.Start(cancellationToken, mstStageId.Value, partyNo.Value, isChallengeAd, staminaBoostCount.Value);
                return StageStartResultTranslator.ToStageStartResultModel(data);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<StageEndResultModel> IStageService.End(
            CancellationToken cancellationToken,
            MasterDataId mstStageId,
            InGameEndBattleLogModel inGameEndBattleLogModel)
        {
            try
            {
                var inGameBattleLogData = InGameEndBattleLogDataTranslator.ToInGameEndBattleLogData(inGameEndBattleLogModel);
                var resultData = await StageApi.End(cancellationToken, mstStageId.Value, inGameBattleLogData);
                return StageEndResultDataTranslator.ToStageEndResultModel(resultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask IStageService.AbortSession(CancellationToken cancellationToken, StageAbortType stageAbortType)
        {
            await StageApi.Abort(cancellationToken, (int)stageAbortType);
        }

        async UniTask IStageService.Cleanup(CancellationToken cancellationToken)
        {
            try
            {
                await StageApi.Cleanup(cancellationToken);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<StageContinueDiamondResultModel> IStageService.ContinueDiamond(
            CancellationToken cancellationToken,
            MasterDataId mstStageId)
        {
            try
            {
                var resultData = await StageApi.ContinueDiamond(cancellationToken, mstStageId.Value);
                return StageContinueDiamondResultDataTranslator.ToStageContinueDiamondResultModel(resultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<StageContinueAdResultModel> IStageService.ContinueAd(
            CancellationToken cancellationToken,
            MasterDataId mstStageId)
        {
            try
            {
                var resultData = await StageApi.ContinueAd(cancellationToken, mstStageId.Value);
                return StageContinueAdResultDataTranslator.ToStageContinueAdResultModel(resultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }
    }
}
