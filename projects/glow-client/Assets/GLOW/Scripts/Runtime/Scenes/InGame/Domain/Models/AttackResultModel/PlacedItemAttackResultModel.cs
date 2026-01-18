using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.AttackResultModel
{
    public record PlacedItemAttackResultModel(
        FieldObjectId AttackerId,
        BattleSide PlacedItemBattleSide,
        KomaId KomaId,
        FieldCoordV2 Pos,
        AttackElement PickUpAttackElement,
        StateEffect StateEffect) : IAttackResultModel
    {
        public static PlacedItemAttackResultModel Empty { get; } = new(
            FieldObjectId.Empty,
            BattleSide.Player,
            KomaId.Empty,
            FieldCoordV2.Empty,
            AttackElement.Empty,
            StateEffect.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
