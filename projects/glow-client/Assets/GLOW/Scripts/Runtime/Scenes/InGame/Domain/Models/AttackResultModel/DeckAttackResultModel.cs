using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.AttackResultModel
{
    public record DeckAttackResultModel(
        MasterDataId TargetCharacterId,
        BattleSide TargetBattleSide,
        FieldObjectId AttackerId,
        StateEffect StateEffect) : IAttackResultModel
    {
        public static DeckAttackResultModel Empty { get; } = new(
            MasterDataId.Empty,
            BattleSide.Player,
            FieldObjectId.Empty,
            StateEffect.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

