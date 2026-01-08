using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants.AdventBattle;
using GLOW.Core.Domain.Models.AdventBattle;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models.LogModel;

namespace GLOW.Scenes.AdventBattle.Domain.Definition.Service
{
    public interface IAdventBattleService
    {
        UniTask Start(
            CancellationToken cancellationToken,
            MasterDataId mstAdventBattleId,
            PartyNo partyNo,
            AdventBattleChallengeType challengeType,
            InGameStartBattleLogModel inGameStartBattleLogModel);

        UniTask<AdventBattleEndResultModel> End(
            CancellationToken cancellationToken,
            MasterDataId mstAdventBattleId,
            InGameEndBattleLogModel inGameLogModel);

        UniTask<AdventBattleAbortResultModel> Abort(
            CancellationToken cancellationToken,
            AdventBattleAbortType abortType);

        UniTask Cleanup(CancellationToken cancellationToken);

        UniTask<AdventBattleTopResultModel> Top(
            CancellationToken cancellationToken,
            MasterDataId mstAdventBattleId);

        UniTask<AdventBattleRankingResultModel> GetRanking(
            CancellationToken cancellationToken,
            MasterDataId mstAdventBattleId,
            bool isPrevious);
        UniTask<AdventBattleInfoResultModel> GetInfo(CancellationToken cancellationToken);
    }
}
