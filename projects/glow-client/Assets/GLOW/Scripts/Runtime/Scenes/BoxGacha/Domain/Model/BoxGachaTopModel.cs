using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.BoxGacha;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.BoxGacha.Domain.Model
{
    public record BoxGachaTopModel(
        MasterDataId MstBoxGachaId,
        BoxGachaName BoxGachaName,
        UnitImageAssetPath DisplayDecoUnitFirst,
        UnitImageAssetPath DisplayDecoEnemyUnitSecond,
        KomaBackgroundAssetPath KomaBackgroundAssetPath,
        BoxGachaInfoModel BoxGachaInfoModel)
    {
        public static BoxGachaTopModel Empty { get; } = new(
            MasterDataId.Empty,
            BoxGachaName.Empty,
            UnitImageAssetPath.Empty,
            UnitImageAssetPath.Empty,
            KomaBackgroundAssetPath.Empty,
            BoxGachaInfoModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}