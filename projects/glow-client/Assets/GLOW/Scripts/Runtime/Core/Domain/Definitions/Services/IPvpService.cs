using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models.LogModel;

namespace GLOW.Core.Domain.Services
{
    public interface IPvpService
    {
        UniTask<PvpTopResultModel> Top(CancellationToken cancellationToken);
        UniTask<PvpStartResultModel> Start(
            CancellationToken cancellationToken,
            string sysPvpSeasonId,
            int isUseItem,
            string opponentMyId,
            int partyNo,
            InGameStartBattleLogModel inGameStartBattleLogModel,
            InGameRandomSeed randomSeed);
        UniTask<PvpEndResultModel> End(
            CancellationToken cancellationToken,
            ContentSeasonSystemId sysPvpSeasonId,
            PvpInGameEndBattleLogModel pvpInGameEndBattleLogModel,
            bool isWin);
        UniTask<PvpResumeResultModel> Resume(CancellationToken cancellationToken);
        UniTask<PvpChangeOpponentResultModel> ChangeOpponent(CancellationToken cancellationToken);
        UniTask<PvpAbortResultModel> Abort(CancellationToken cancellationToken);
        UniTask Cleanup(CancellationToken cancellationToken);
        UniTask<PvpRankingResultModel> Ranking(CancellationToken cancellationToken, bool isPreviousSeason);
    }
}
