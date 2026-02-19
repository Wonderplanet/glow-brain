using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaList.Domain.Definition.Service
{
    public interface IGachaService
    {
        UniTask<GachaPrizeResultModel> Prize(CancellationToken cancellationToken, MasterDataId oprGachaId);
        UniTask<GachaDrawResultModel> DrawByAd(CancellationToken cancellationToken, MasterDataId oprGachaId, GachaPlayedCount playedCount);
        UniTask<GachaDrawResultModel> DrawByDiamond(CancellationToken cancellationToken, MasterDataId oprGachaId, GachaPlayedCount playedCount, GachaDrawCount drawCount, CostAmount costAmount);
        UniTask<GachaDrawResultModel> DrawByPaidDiamond(CancellationToken cancellationToken, MasterDataId oprGachaId, GachaPlayedCount playedCount, GachaDrawCount drawCount, CostAmount costAmount);
        UniTask<GachaDrawResultModel> DrawByItem(CancellationToken cancellationToken, MasterDataId oprGachaId, GachaPlayedCount playedCount, GachaDrawCount drawCount, MasterDataId costId, CostAmount costAmount);
        UniTask<GachaDrawResultModel> DrawByFree(CancellationToken cancellationToken, MasterDataId oprGachaId, GachaPlayedCount playedCount);
        UniTask<GachaHistoryResultModel> History(CancellationToken cancellationToken);

    }
}
