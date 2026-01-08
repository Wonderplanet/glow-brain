using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class DeckUnitSpecialAttackExecutor : IDeckUnitSpecialAttackExecutor
    {
        public DeckUnitSpecialAttackResult UseSpecialAttack(
            DeckUnitModel deckUnit,
            IReadOnlyList<CharacterUnitModel> units,
            BattleSide battleSide)
        {
            var unit = units.FirstOrDefault(
                unit => unit.BattleSide == battleSide && unit.CharacterId == deckUnit.CharacterId,
                CharacterUnitModel.Empty);

            if (unit.IsEmpty()) return DeckUnitSpecialAttackResult.Empty;
            if (unit.SpecialAttack.IsEmpty()) return DeckUnitSpecialAttackResult.Empty;

            // CharacterUnitModel更新
            var updatedUnit = unit with { NextAttackKind = AttackKind.Special };

            // DeckUnitModel更新
            var updatedDeckUnit = deckUnit with
            {
                CurrentSpecialAttackCoolTime = deckUnit.SpecialAttackCoolTime,
                RemainingSpecialAttackCoolTime = deckUnit.SpecialAttackCoolTime,
                IsSpecialAttackReady = true
            };

            return new DeckUnitSpecialAttackResult(updatedDeckUnit, updatedUnit, unit);
        }
    }
}

