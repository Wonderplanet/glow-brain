using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.Models;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class HomeHeaderIconUseCase
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstEmblemRepository MstEmblemRepository { get; }

        public HomeHeaderIconUseCaseModel GetHomeHeaderIcon(MasterDataId mstUnitId, MasterDataId mstEmblemId)
        {
            var mstUnitAssetKey = UnitAssetKey.Empty;
            if (!mstUnitId.IsEmpty())
            {
                mstUnitAssetKey = MstCharacterDataRepository.GetCharacter(mstUnitId).AssetKey;
            }

            var emblemAssetKey = EmblemAssetKey.Empty;
            if (!mstEmblemId.IsEmpty())
            {
                emblemAssetKey = MstEmblemRepository.GetMstEmblemFirstOrDefault(mstEmblemId).AssetKey;
            }

            return new HomeHeaderIconUseCaseModel(mstUnitAssetKey, emblemAssetKey);
        }
    }
}
