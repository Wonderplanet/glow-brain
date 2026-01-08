using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Encyclopedia;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Services
{
    public interface IEncyclopediaService
    {
        UniTask<EncyclopediaReceiveRewardResultModel> ReceiveReward(CancellationToken cancellationToken, IReadOnlyList<MasterDataId> mstUnitEncyclopediaRewardIds);
        UniTask<EncyclopediaReceiveFirstCollectionRewardResultModel> ReceiveFirstCollectionReward(CancellationToken cancellationToken, EncyclopediaType type, MasterDataId mstId);
    }
}
