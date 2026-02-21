using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class DeckUpdateProcess : IDeckUpdateProcess
    {
        public DeckUpdateProcessResult Update(
            IReadOnlyList<DeckUnitModel> deckUnits,
            IReadOnlyList<DeckUnitModel> pvpOpponentDeckUnits,
            IReadOnlyList<CharacterUnitModel> units,
            IReadOnlyList<CharacterUnitModel> deadUnits,
            IReadOnlyList<SpecialUnitModel> specialUnits,
            IReadOnlyList<SpecialUnitModel> removedSpecialUnits,
            TickCount tickCount)
        {
            var updateDeckUnits = deckUnits
                .Select(deckUnit => deckUnit.RoleType != CharacterUnitRoleType.Special
                    ? UpdateDeckUnitModel(
                        deckUnit,
                        units,
                        deadUnits,
                        BattleSide.Player,
                        tickCount)
                    : UpdateSpecialUnitDeckUnitModel(
                        deckUnit,
                        specialUnits,
                        BattleSide.Player,
                        tickCount))
                .ToList();

            var updatePvpOpponentDeckUnits = pvpOpponentDeckUnits
                .Select(deckUnit => deckUnit.RoleType != CharacterUnitRoleType.Special
                    ? UpdateDeckUnitModel(
                        deckUnit,
                        units,
                        deadUnits,
                        BattleSide.Enemy,
                        tickCount)
                    : UpdateSpecialUnitDeckUnitModel(
                        deckUnit,
                        specialUnits,
                        BattleSide.Enemy,
                        tickCount))
                .ToList();

            return new DeckUpdateProcessResult(
                updateDeckUnits,
                updatePvpOpponentDeckUnits);
        }

        DeckUnitModel UpdateDeckUnitModel(
            DeckUnitModel deckUnit,
            IReadOnlyList<CharacterUnitModel> units,
            IReadOnlyList<CharacterUnitModel> deadUnits,
            BattleSide battleSide,
            TickCount tickCount)
        {
            var isDead = deadUnits.Any(unit =>
                unit.BattleSide == battleSide &&
                unit.CharacterId == deckUnit.CharacterId);

            var isSummoned = !isDead && deckUnit.IsSummoned;

            var remainingSummonCoolTime = isSummoned
                ? deckUnit.RemainingSummonCoolTime
                : deckUnit.RemainingSummonCoolTime - tickCount;

            var unitModel = isSummoned
                ? units.FirstOrDefault(
                    unit => unit.BattleSide == battleSide && unit.CharacterId == deckUnit.CharacterId,
                    CharacterUnitModel.Empty)
                : CharacterUnitModel.Empty;

            var isSpecialAttackReady = unitModel.IsSpecialAttackReady();

            var remainingSpecialAttackCoolTime = isSpecialAttackReady
                ? deckUnit.RemainingSpecialAttackCoolTime
                : deckUnit.RemainingSpecialAttackCoolTime - tickCount;

            return deckUnit with
            {
                RemainingSummonCoolTime = remainingSummonCoolTime,
                IsSummoned = isSummoned,
                RemainingSpecialAttackCoolTime = remainingSpecialAttackCoolTime,
                IsSpecialAttackReady = isSpecialAttackReady,
            };
        }

        DeckUnitModel UpdateSpecialUnitDeckUnitModel(
            DeckUnitModel deckUnit,
            IReadOnlyList<SpecialUnitModel> specialUnits,
            BattleSide battleSide,
            TickCount tickCount)
        {
            var unitModel = specialUnits.FirstOrDefault(
                unit => unit.BattleSide == battleSide && unit.CharacterId == deckUnit.CharacterId,
                SpecialUnitModel.Empty);

            var isSummoned = !unitModel.IsEmpty();

            var isSpecialAttackReady = unitModel.SpecialUnitSpecialAttackChargeFlag;

            return deckUnit with
            {
                IsSummoned = isSummoned,
                IsSpecialAttackReady = isSpecialAttackReady,
            };
        }
    }
}
