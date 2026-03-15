using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.BoxGacha.Presentation.ViewModel
{
    public record BoxGachaTopViewModel(
        MasterDataId MstBoxGachaId,
        BoxGachaName BoxGachaName,
        UnitImageAssetPath DisplayDecoUnitFirst,
        UnitImageAssetPath DisplayDecoEnemyUnitSecond,
        KomaBackgroundAssetPath KomaBackgroundAssetPath,
        BoxGachaInfoViewModel BoxGachaInfoViewModel)
    {
        public static BoxGachaTopViewModel Empty { get; } = new(
            MasterDataId.Empty,
            BoxGachaName.Empty,
            UnitImageAssetPath.Empty,
            UnitImageAssetPath.Empty,
            KomaBackgroundAssetPath.Empty,
            BoxGachaInfoViewModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}