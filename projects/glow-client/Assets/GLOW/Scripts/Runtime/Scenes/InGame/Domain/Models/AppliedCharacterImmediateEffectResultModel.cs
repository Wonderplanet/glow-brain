using GLOW.Core.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    /// <summary>
    /// 盤面上キャラに対する即時効果の適用結果
    /// </summary>
    public record AppliedCharacterImmediateEffectResultModel(
        FieldObjectId TargetId,
        BattleSide TargetBattleSide,
        StateEffectType StateEffectType)
    {
        public static AppliedCharacterImmediateEffectResultModel Empty { get; } = new(
            FieldObjectId.Empty,
            BattleSide.Player,
            StateEffectType.None);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}

