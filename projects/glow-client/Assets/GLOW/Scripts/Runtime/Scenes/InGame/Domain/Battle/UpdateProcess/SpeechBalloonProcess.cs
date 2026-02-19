using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class SpeechBalloonProcess : ISpeechBalloonProcess
    {
        [Inject] IRandomProvider RandomProvider { get; }

        public IReadOnlyList<UnitSpeechBalloonModel> Update(IReadOnlyList<CharacterUnitModel> units)
        {
            var speechBalloons = new List<UnitSpeechBalloonModel>();

            foreach (var unit in units)
            {
                if (unit.IsStateEnd(UnitActionState.Summoning))
                {
                    var speechBalloon = ChoiceSummonSpeechBalloon(unit);
                    if (!speechBalloon.IsEmpty()) speechBalloons.Add(speechBalloon);
                }

                if (unit.IsStateStart(UnitActionState.AttackCharge))
                {
                    var specialAttackChargeSpeechBalloons = unit.SpeechBalloons
                        .Where(model => model.SpeechBalloon.ConditionType == SpeechBalloonConditionType.SpecialAttackCharge);

                    speechBalloons.AddRange(specialAttackChargeSpeechBalloons);
                }

                if (unit.IsStateStart(UnitActionState.SpecialAttack))
                {
                    var specialAttackSpeechBalloon = unit.SpeechBalloons
                        .Where(model => model.SpeechBalloon.ConditionType == SpeechBalloonConditionType.SpecialAttack);

                    speechBalloons.AddRange(specialAttackSpeechBalloon);
                }
            }

            return speechBalloons;
        }

        UnitSpeechBalloonModel ChoiceSummonSpeechBalloon(CharacterUnitModel unit)
        {
            var summonSpeechBalloons = unit.SpeechBalloons
                .Where(model => model.SpeechBalloon.ConditionType == SpeechBalloonConditionType.Summon)
                .ToList();

            if (summonSpeechBalloons.Count == 0) return UnitSpeechBalloonModel.Empty;

            var index = RandomProvider.Range(summonSpeechBalloons.Count);
            return summonSpeechBalloons[index];
        }
    }
}
