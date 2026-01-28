using System;
using GLOW.Scenes.InGame.Presentation.Constants;

namespace GLOW.Scenes.InGame.Presentation.ValueObjects
{
    public record OutpostSDAnimation(OutpostSDAnimationType type, string Name, int Priority, bool IsLoop)
    {
        public static OutpostSDAnimation Empty { get; } = new (OutpostSDAnimationType.Empty, String.Empty, -1, false);
        public static OutpostSDAnimation Attack { get; } = new (OutpostSDAnimationType.Attack, "attack", 3, false);
        public static OutpostSDAnimation MirrorAttack { get; } = new (OutpostSDAnimationType.Attack, "attack_mir", 3, false);
        public static OutpostSDAnimation Beam { get; } = new (OutpostSDAnimationType.Beam, "beam", 4, false);
        public static OutpostSDAnimation MirrorBeam { get; } = new (OutpostSDAnimationType.Beam, "beam_mir", 4, false);
        public static OutpostSDAnimation Damage { get; } = new (OutpostSDAnimationType.Damage, "damage", 2, false);
        public static OutpostSDAnimation MirrorDamage { get; } = new (OutpostSDAnimationType.Damage, "damage_mir", 2, false);
        public static OutpostSDAnimation Death { get; } = new (OutpostSDAnimationType.Death, "death", 5, false);
        public static OutpostSDAnimation MirrorDeath { get; } = new (OutpostSDAnimationType.Death, "death_mir", 5, false);
        public static OutpostSDAnimation Wait { get; } = new (OutpostSDAnimationType.Wait, "wait", 0, true);
        public static OutpostSDAnimation MirrorWait { get; } = new (OutpostSDAnimationType.Wait, "wait_mir", 0, true);
        public static OutpostSDAnimation Pinch { get; } = new(OutpostSDAnimationType.Pinch, "pinch", 1, true);
        public static OutpostSDAnimation MirrorPinch { get; } = new(OutpostSDAnimationType.Pinch, "pinch_mir", 1, true);

        public string Name { get; } = Name;
        public bool IsLoop { get; } = IsLoop;

        public override string ToString()
        {
            return Name;
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
