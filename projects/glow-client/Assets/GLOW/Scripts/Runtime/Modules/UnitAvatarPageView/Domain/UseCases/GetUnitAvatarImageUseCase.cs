using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.UnitAvatarPageView.Domain.Interfaces;
using GLOW.Modules.UnitAvatarPageView.Domain.Models;
using Zenject;

namespace GLOW.Modules.UnitAvatarPageView.Domain.UseCases
{
    public class GetUnitAvatarImageUseCase : IGetSpineAvatarImageUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }

        public UnitAvatarPageModel GetImagePath(MasterDataId mstUnitId)
        {
            var mstUnit = MstCharacterDataRepository.GetCharacter(mstUnitId);
            return new UnitAvatarPageModel(
                UnitImageAssetPath.FromAssetKey(mstUnit.AssetKey),
                mstUnit.Color,
                PhantomizedFlag.False);
        }
    }
}
