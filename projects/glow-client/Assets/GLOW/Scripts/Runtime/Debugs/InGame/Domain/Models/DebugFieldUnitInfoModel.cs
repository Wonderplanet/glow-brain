#if GLOW_INGAME_DEBUG
using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Debugs.InGame.Domain.Models
{
    public record DebugFieldUnitInfoModel(
        FieldObjectId FieldObjectId,
        CharacterName Name,
        CharacterUnitKind UnitKind,
        DebugUnitStatusModel Status
    )
    {
        public static readonly DebugFieldUnitInfoModel Empty = new (
            FieldObjectId.Empty,
            CharacterName.Empty,
            CharacterUnitKind.Normal,
            DebugUnitStatusModel.Empty
        );
    }
}
#endif // GLOW_INGAME_DEBUG
