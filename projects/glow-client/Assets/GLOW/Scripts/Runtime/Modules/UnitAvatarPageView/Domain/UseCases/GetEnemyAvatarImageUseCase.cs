using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.UnitAvatarPageView.Domain.Interfaces;
using GLOW.Modules.UnitAvatarPageView.Domain.Models;
using Zenject;

namespace GLOW.Modules.UnitAvatarPageView.Domain.UseCases
{
    public class GetEnemyAvatarImageUseCase : IGetSpineAvatarImageUseCase
    {
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }

        public UnitAvatarPageModel GetImagePath(MasterDataId mstUnitId)
        {
            var mstEnemy = MstEnemyCharacterDataRepository.GetEnemyCharacter(mstUnitId);
            return new UnitAvatarPageModel(
                UnitImageAssetPath.FromAssetKey(mstEnemy.AssetKey),
                CharacterColor.Colorless,
                mstEnemy.IsPhantomized);
        }
    }
}
