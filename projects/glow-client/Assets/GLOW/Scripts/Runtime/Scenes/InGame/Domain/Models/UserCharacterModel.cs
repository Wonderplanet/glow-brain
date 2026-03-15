using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record UserCharacterModel(
        UserDataId Id,
        MasterDataId CharacterId);
}
