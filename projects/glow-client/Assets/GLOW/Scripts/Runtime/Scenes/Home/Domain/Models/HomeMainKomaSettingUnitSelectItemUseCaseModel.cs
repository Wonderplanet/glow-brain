using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeMainKomaSettingUnitSelectItemUseCaseModel(
        MasterDataId MstUnitId,
        CharacterIconAssetPath AssetPath,
        HomeMainKomaSettingUnitStatus Status);
}