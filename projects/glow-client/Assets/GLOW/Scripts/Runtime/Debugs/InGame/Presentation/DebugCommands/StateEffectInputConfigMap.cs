#if GLOW_INGAME_DEBUG
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;

namespace GLOW.Debugs.InGame.Presentation.DebugCommands
{
    public static class StateEffectInputConfigMap
    {
        public static readonly Dictionary<StateEffectType, StateEffectInputConfig> Map = new()
        {
            { StateEffectType.AttackPowerUp, new StateEffectInputConfig {
                EffectTypeLabel = "攻撃力アップ",
                ParameterLabel = "増加量％",
                ParameterDefault = "100",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.AttackPowerDown, new StateEffectInputConfig {
                EffectTypeLabel = "攻撃力ダウン",
                ParameterLabel = "減少量％",
                ParameterDefault = "50",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.DamageCut, new StateEffectInputConfig {
                EffectTypeLabel = "ダメージ軽減",
                ParameterLabel = "カット率％",
                ParameterDefault = "50",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.AttackSpeedDown, new StateEffectInputConfig {
                EffectTypeLabel = "攻撃スピードダウン",
                ParameterLabel = "減少率％",
                ParameterDefault = "50",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.MoveSpeedUp, new StateEffectInputConfig {
                EffectTypeLabel = "移動スピードアップ",
                ParameterLabel = "増加量％",
                ParameterDefault = "100",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.MoveSpeedDown, new StateEffectInputConfig {
                EffectTypeLabel = "移動スピードダウン",
                ParameterLabel = "減少量％",
                ParameterDefault = "50",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.SlipDamage, new StateEffectInputConfig {
                EffectTypeLabel = "継続ダメージ",
                ParameterLabel = "ダメージ量",
                ParameterDefault = "10",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.SlipDamageKomaBlock, new StateEffectInputConfig {
                EffectTypeLabel = "ダメージコマ無効",
                ParameterLabel = "不使用",
                ParameterDefault = "0",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.AttackPowerDownKomaBlock, new StateEffectInputConfig {
                EffectTypeLabel = "パワーダウンコマ無効",
                ParameterLabel = "不使用",
                ParameterDefault = "0",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.GustKomaBlock, new StateEffectInputConfig {
                EffectTypeLabel = "突風コマ無効",
                ParameterLabel = "不使用",
                ParameterDefault = "0",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.AttackPowerUpKomaBoost, new StateEffectInputConfig {
                EffectTypeLabel = "パワーアップコマ効果ブースト",
                ParameterLabel = "増加量％",
                ParameterDefault = "100",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.AttackPowerUpInNormalKoma, new StateEffectInputConfig {
                EffectTypeLabel = "通常コマ時攻撃力アップ",
                ParameterLabel = "増加量％",
                ParameterDefault = "100",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.MoveSpeedUpInNormalKoma, new StateEffectInputConfig {
                EffectTypeLabel = "通常コマ時移動スピードUP",
                ParameterLabel = "増加量％",
                ParameterDefault = "100",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.DamageCutInNormalKoma, new StateEffectInputConfig {
                EffectTypeLabel = "通常コマ時ガード",
                ParameterLabel = "カット率％",
                ParameterDefault = "50",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.Poison, new StateEffectInputConfig {
                EffectTypeLabel = "毒",
                ParameterLabel = "ダメージ値",
                ParameterDefault = "10",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.PoisonBlock, new StateEffectInputConfig {
                EffectTypeLabel = "毒耐性",
                ParameterLabel = "不使用",
                ParameterDefault = "0",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.Burn, new StateEffectInputConfig {
                EffectTypeLabel = "火傷",
                ParameterLabel = "ダメージ値",
                ParameterDefault = "10",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.BurnBlock, new StateEffectInputConfig {
                EffectTypeLabel = "火傷耐性",
                ParameterLabel = "不使用",
                ParameterDefault = "0",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.KnockBackBlock, new StateEffectInputConfig {
                EffectTypeLabel = "ノックバック耐性",
                ParameterLabel = "不使用",
                ParameterDefault = "0",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.Stun, new StateEffectInputConfig {
                EffectTypeLabel = "スタン",
                ParameterLabel = "不使用",
                ParameterDefault = "0",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.Guts, new StateEffectInputConfig {
                EffectTypeLabel = "根性",
                ParameterLabel = "不使用",
                ParameterDefault = "0",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.PoisonDamageCut, new StateEffectInputConfig {
                EffectTypeLabel = "毒ダメージ軽減",
                ParameterLabel = "カット率％",
                ParameterDefault = "50",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.BurnDamageCut, new StateEffectInputConfig {
                EffectTypeLabel = "火傷ダメージ軽減",
                ParameterLabel = "カット率％",
                ParameterDefault = "50",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.StunBlock, new StateEffectInputConfig {
                EffectTypeLabel = "スタン耐性",
                ParameterLabel = "不使用",
                ParameterDefault = "0",
                ConditionValue1Label = "ロール条件",
                ConditionValue1Default = "All",
                ConditionValue2Label = "キャラ属性条件",
                ConditionValue2Default = "All"
            }},
            { StateEffectType.Freeze, new StateEffectInputConfig {
                EffectTypeLabel = "凍結",
                ParameterLabel = "不使用",
                ParameterDefault = "0",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.FreezeBlock, new StateEffectInputConfig {
                EffectTypeLabel = "凍結耐性",
                ParameterLabel = "不使用",
                ParameterDefault = "0",
                ConditionValue1Label = "ロール条件",
                ConditionValue1Default = "All",
                ConditionValue2Label = "キャラ属性条件",
                ConditionValue2Default = "All"
            }},
            { StateEffectType.RushAttackPowerUp, new StateEffectInputConfig {
                EffectTypeLabel = "ジャンブルラッシュ攻撃力アップ",
                ParameterLabel = "増加量％",
                ParameterDefault = "100",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.RegenerationByFixed, new StateEffectInputConfig {
                EffectTypeLabel = "継続回復（固定量）",
                ParameterLabel = "回復量",
                ParameterDefault = "10",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.RegenerationByMaxHpPercentage, new StateEffectInputConfig {
                EffectTypeLabel = "継続回復（最大HP割合）",
                ParameterLabel = "回復量％",
                ParameterDefault = "10",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.Weakening, new StateEffectInputConfig {
                EffectTypeLabel = "弱体化",
                ParameterLabel = "ダメージ増加率(%)",
                ParameterDefault = "20",
                ConditionValue1Label = "不使用",
                ConditionValue1Default = "0",
                ConditionValue2Label = "不使用",
                ConditionValue2Default = "0"
            }},
            { StateEffectType.WeakeningBlock, new StateEffectInputConfig {
                EffectTypeLabel = "弱体化無効化",
                ParameterLabel = "不使用",
                ParameterDefault = "0",
                ConditionValue1Label = "ロール条件",
                ConditionValue1Default = "All",
                ConditionValue2Label = "キャラ属性条件",
                ConditionValue2Default = "All"
            }},
        };
    }
}
#endif
