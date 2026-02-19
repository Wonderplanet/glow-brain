using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models.BoxGacha;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Core.Domain.Services
{
    public interface IBoxGachaService
    {
        UniTask<BoxGachaInfoResultModel> Info(CancellationToken cancellationToken, MasterDataId mstBoxGachaId);
        UniTask<BoxGachaDrawResultModel> Draw(
            CancellationToken cancellationToken,
            MasterDataId mstBoxGachaId,
            GachaDrawCount drawCount,
            BoxLevel currentBoxLevel);
        UniTask<BoxGachaResetResultModel> Reset(
            CancellationToken cancellationToken,
            MasterDataId mstBoxGachaId,
            BoxLevel currentBoxLevel);
    }
}