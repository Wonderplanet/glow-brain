using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Stage;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Scenes.InGame.Domain.Models.LogModel;

namespace GLOW.Core.Domain.Services
{
    public interface IStageService
    {
        UniTask<StageStartResultModel> Start(
            CancellationToken cancellationToken,
            MasterDataId mstStageId,
            PartyNo partyNo,
            bool isChallengeAd,
            StaminaBoostCount staminaBoostCount);

        UniTask<StageEndResultModel> End(
            CancellationToken cancellationToken,
            MasterDataId mstStageId,
            InGameEndBattleLogModel inGameEndBattleLogModel);

        UniTask AbortSession(CancellationToken cancellationToken, StageAbortType stageAbortType);
        UniTask Cleanup(CancellationToken cancellationToken);
        UniTask<StageContinueDiamondResultModel> ContinueDiamond(CancellationToken cancellationToken, MasterDataId mstStageId);
        UniTask<StageContinueAdResultModel> ContinueAd(CancellationToken cancellationToken, MasterDataId mstStageId);
    }
}
