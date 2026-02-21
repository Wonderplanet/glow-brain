using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public record DeckUnitSpecialAttackResult(
        DeckUnitModel UpdatedDeckUnit, 
        CharacterUnitModel UpdatedUnit,
        CharacterUnitModel Unit) // 更新前のCharacterUnitModel
    {
        public static DeckUnitSpecialAttackResult Empty { get; } = new DeckUnitSpecialAttackResult(
            DeckUnitModel.Empty,
            CharacterUnitModel.Empty,
            CharacterUnitModel.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}