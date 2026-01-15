#if GLOW_INGAME_DEBUG
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Debugs.InGame.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Debugs.InGame.Domain.Models
{
    public record DebugInGameLogDamageModel(
        BattleSide Side, 
        DamageDebugLogTargetName TargetName,
        AttackHitType AttackHitType,
        AttackDamageType AttackDamageType,
        Damage Damage,
        Heal Heal,
        HP BeforeHp,
        HP AfterHp
    );
}
#endif
