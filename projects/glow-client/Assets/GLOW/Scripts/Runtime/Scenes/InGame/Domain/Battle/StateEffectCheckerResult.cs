using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public record StateEffectCheckerResult(
        IReadOnlyList<IStateEffectModel> UpdatedStateEffects,
        IReadOnlyList<StateEffectParameter> Parameters,
        EffectActivatedFlag IsEffectActivated)
    {
        public static StateEffectCheckerResult Empty { get; } = new(
            new List<IStateEffectModel>(),
            new List<StateEffectParameter>(),
            EffectActivatedFlag.False);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
