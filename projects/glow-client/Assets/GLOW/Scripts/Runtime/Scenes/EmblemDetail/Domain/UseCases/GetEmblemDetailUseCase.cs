using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.EmblemDetail.Domain.Models;
using Zenject;

namespace GLOW.Scenes.EmblemDetail.Domain.UseCases
{
    public class GetEmblemDetailUseCase
    {
        [Inject] IMstEmblemRepository MstEmblemRepository { get; }

        public EmblemDetailModel GetEmblemDetail(MasterDataId mstEmblemId)
        {
            var mstEmblem = MstEmblemRepository.GetMstEmblemFirstOrDefault(mstEmblemId);

            return new EmblemDetailModel(
                EmblemIconAssetPath.FromAssetKey(mstEmblem.AssetKey),
                mstEmblem.Name,
                mstEmblem.Description);
        }
    }
}
