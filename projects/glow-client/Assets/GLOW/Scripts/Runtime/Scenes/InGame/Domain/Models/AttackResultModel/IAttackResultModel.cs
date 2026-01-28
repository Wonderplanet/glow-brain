using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models.AttackResultModel
{
    public interface IAttackResultModel
    {
        FieldObjectId AttackerId { get; }
        StateEffect StateEffect { get; }
        bool IsEmpty();
    }
}
