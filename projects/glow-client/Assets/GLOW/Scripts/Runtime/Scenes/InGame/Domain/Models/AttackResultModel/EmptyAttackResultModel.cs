using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.AttackResultModel
{
    public record EmptyAttackResultModel(FieldObjectId AttackerId, StateEffect StateEffect) : IAttackResultModel
    {
        public static EmptyAttackResultModel Empty { get; } = new(FieldObjectId.Empty, StateEffect.Empty);
        public bool IsEmpty()
        {
            return true;
        }
    }
}
