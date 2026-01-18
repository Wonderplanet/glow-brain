using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Scenes.BoxGacha.Domain.Model
{
    public record BoxGachaTopModel(
        MasterDataId MstBoxGachaId,
        UnitImageAssetPath DisplayDecoUnitFirst,
        UnitImageAssetPath DisplayDecoEnemyUnitSecond,
        KomaBackgroundAssetPath KomaBackgroundAssetPath,
        BoxGachaInfoModel BoxGachaInfoModel)
    {
        public static BoxGachaTopModel Empty { get; } = new(
            MasterDataId.Empty,
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