#if GLOW_INGAME_DEBUG
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Debugs.InGame.Domain.Models;

namespace GLOW.Debugs.InGame.Domain.Constants.DebugSkillTemplates
{
    // その他テンプレート
    public static class DebugOthersTemplates
    {
        public static IReadOnlyList<(string name, IReadOnlyList<DebugAttackElementData> elementData)> GetTemplates()
        {
            return new List<(string, IReadOnlyList<DebugAttackElementData>)>
            {
                ("敵味方全体_バフデバフ一通り付与", CreateAllBuffsAndDebuffs()),
                ("敵味方全体_バフデバフ一通り解除", CreateRemoveAllBuffDebuff()),
            };
        }

        // 対象検知用のテンプレート
        static DebugAttackElementData CreateFirstAttackElementData()
        {
            return new DebugAttackElementData(
                Id: string.Empty,
                MstAttackId: string.Empty,
                SortOrder: 1,
                AttackType: AttackType.Direct,
                Target: AttackTarget.Friend,
                TargetType: AttackTargetType.All,
                TargetColors: "All",
                TargetRoles: "All",
                TargetMstSeriesIds: string.Empty,
                TargetMstCharacterIds: string.Empty,
                RangeStartType: AttackRangePointType.Page,
                RangeStartParameter: 0,
                RangeEndType: AttackRangePointType.Page,
                RangeEndParameter: 0,
                MaxTargetCount: -1,
                DamageType: AttackDamageType.None,
                HitType: AttackHitType.Normal,
                HitParameter1: 0,
                HitParameter2: 0,
                HitEffectId: string.Empty,
                IsHitStop: false,
                Probability: 100,
                PowerParameterType: AttackPowerParameterType.Percentage,
                PowerParameter: 0,
                EffectType: StateEffectType.None,
                EffectiveCount: -1,
                EffectiveDuration: 0,
                EffectParameter: 0,
                EffectValue: string.Empty,
                EffectTriggerRoles: string.Empty,
                EffectTriggerColors: string.Empty,
                AttackDelay: 0
            );
        }

        // ベーステンプレート
        static DebugAttackElementData CreateBaseAttackElementData()
        {
            return new DebugAttackElementData(
                Id: string.Empty,
                MstAttackId: string.Empty,
                SortOrder: 2,
                AttackType: AttackType.Direct,
                Target: AttackTarget.Friend,
                TargetType: AttackTargetType.All,
                TargetColors: "All",
                TargetRoles: "All",
                TargetMstSeriesIds: string.Empty,
                TargetMstCharacterIds: string.Empty,
                RangeStartType: AttackRangePointType.Page,
                RangeStartParameter: 0,
                RangeEndType: AttackRangePointType.Page,
                RangeEndParameter: 0,
                MaxTargetCount: -1,
                DamageType: AttackDamageType.None,
                HitType: AttackHitType.Normal,
                HitParameter1: 0,
                HitParameter2: 0,
                HitEffectId: string.Empty,
                IsHitStop: false,
                Probability: 100,
                PowerParameterType: AttackPowerParameterType.Percentage,
                PowerParameter: 0,
                EffectType: StateEffectType.None,
                EffectiveCount: -1,
                EffectiveDuration: -1,
                EffectParameter: 0,
                EffectValue: string.Empty,
                EffectTriggerRoles: string.Empty,
                EffectTriggerColors: string.Empty,
                AttackDelay: 0
            );
        }

        // 敵味方全体_バフデバフ一通り付与
        static IReadOnlyList<DebugAttackElementData> CreateAllBuffsAndDebuffs()
        {
            return new[]
            {
                CreateFirstAttackElementData(),

                // SortOrder 1: 攻撃力UP (Friend)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 1,
                    EffectType = StateEffectType.AttackPowerUp,
                    EffectParameter = 22
                },
                // SortOrder 1: 攻撃力UP (Foe)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 1,
                    Target = AttackTarget.Foe,
                    EffectType = StateEffectType.AttackPowerUp,
                    EffectParameter = 22
                },

                // SortOrder 2: 被ダメカット (Friend)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 2,
                    EffectType = StateEffectType.DamageCut,
                    EffectParameter = 10
                },
                // SortOrder 2: 被ダメカット (Foe)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 2,
                    Target = AttackTarget.Foe,
                    EffectType = StateEffectType.DamageCut,
                    EffectParameter = 10
                },

                // SortOrder 3: 移動速度UP (Friend)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 3,
                    PowerParameter = 100,
                    EffectType = StateEffectType.MoveSpeedUp,
                    EffectParameter = 50
                },
                // SortOrder 3: 移動速度UP (Foe)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 3,
                    Target = AttackTarget.Foe,
                    PowerParameter = 100,
                    EffectType = StateEffectType.MoveSpeedUp,
                    EffectParameter = 50
                },

                // SortOrder 4: 固定値回復 (Friend)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 4,
                    PowerParameterType = AttackPowerParameterType.Fixed,
                    EffectType = StateEffectType.RegenerationByFixed,
                    EffectParameter = 100
                },
                // SortOrder 4: 固定値回復 (Foe)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 4,
                    Target = AttackTarget.Foe,
                    PowerParameterType = AttackPowerParameterType.Fixed,
                    EffectType = StateEffectType.RegenerationByFixed,
                    EffectParameter = 100
                },

                // SortOrder 5: HP割合回復 (Friend)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 5,
                    PowerParameter = 100,
                    EffectType = StateEffectType.RegenerationByMaxHpPercentage,
                    EffectParameter = 10
                },
                // SortOrder 5: HP割合回復 (Foe)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 5,
                    Target = AttackTarget.Foe,
                    PowerParameter = 100,
                    EffectType = StateEffectType.RegenerationByMaxHpPercentage,
                    EffectParameter = 10
                },

                // SortOrder 6: 攻撃力DOWN (Friend)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 6,
                    PowerParameter = 100,
                    EffectType = StateEffectType.AttackPowerDown,
                    EffectParameter = 10
                },
                // SortOrder 6: 攻撃力DOWN (Foe)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 6,
                    Target = AttackTarget.Foe,
                    PowerParameter = 100,
                    EffectType = StateEffectType.AttackPowerDown,
                    EffectParameter = 10
                },

                // SortOrder 7: 攻撃速度DOWN (Friend)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 7,
                    PowerParameter = 100,
                    EffectType = StateEffectType.AttackSpeedDown,
                    EffectParameter = 50
                },
                // SortOrder 7: 攻撃速度DOWN (Foe)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 7,
                    Target = AttackTarget.Foe,
                    PowerParameter = 100,
                    EffectType = StateEffectType.AttackSpeedDown,
                    EffectParameter = 50
                },

                // SortOrder 8: 移動速度DOWN (Friend)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 8,
                    PowerParameter = 100,
                    EffectType = StateEffectType.MoveSpeedDown,
                    EffectParameter = 50
                },
                // SortOrder 8: 移動速度DOWN (Foe)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 8,
                    Target = AttackTarget.Foe,
                    PowerParameter = 100,
                    EffectType = StateEffectType.MoveSpeedDown,
                    EffectParameter = 50
                },

                // SortOrder 9: 毒 (Friend)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 9,
                    PowerParameter = 100,
                    EffectType = StateEffectType.Poison,
                    EffectParameter = 5
                },
                // SortOrder 9: 毒 (Foe)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 9,
                    Target = AttackTarget.Foe,
                    PowerParameter = 100,
                    EffectType = StateEffectType.Poison,
                    EffectParameter = 5
                },

                // SortOrder 10: 火傷 (Friend)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 10,
                    PowerParameter = 100,
                    EffectType = StateEffectType.Burn,
                    EffectParameter = 5
                },
                // SortOrder 10: 火傷 (Foe)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 10,
                    Target = AttackTarget.Foe,
                    PowerParameter = 100,
                    EffectType = StateEffectType.Burn,
                    EffectParameter = 5
                },

                // SortOrder 11: 弱体化 (Friend)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 11,
                    PowerParameterType = AttackPowerParameterType.Fixed,
                    EffectType = StateEffectType.Weakening,
                    EffectParameter = 30
                },
                // SortOrder 11: 弱体化 (Foe)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 11,
                    Target = AttackTarget.Foe,
                    PowerParameterType = AttackPowerParameterType.Fixed,
                    EffectType = StateEffectType.Weakening,
                    EffectParameter = 30
                },

                // SortOrder 12: スタン (Friend)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 12,
                    HitType = AttackHitType.Stun,
                    HitParameter1 = 5000,
                    HitParameter2 = 100
                },
                // SortOrder 12: スタン (Foe)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 12,
                    Target = AttackTarget.Foe,
                    HitType = AttackHitType.Stun,
                    HitParameter1 = 5000,
                    HitParameter2 = 100
                },

                // SortOrder 13: 氷結 (Friend)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 13,
                    HitType = AttackHitType.Freeze,
                    HitParameter1 = 5000,
                    HitParameter2 = 100
                },
                // SortOrder 13: 氷結 (Foe)
                CreateBaseAttackElementData() with
                {
                    SortOrder = 13,
                    Target = AttackTarget.Foe,
                    HitType = AttackHitType.Freeze,
                    HitParameter1 = 5000,
                    HitParameter2 = 100
                },
            };
        }

        // 敵味方全体_バフデバフ一通り解除
        static IReadOnlyList<DebugAttackElementData> CreateRemoveAllBuffDebuff()
        {
            return new[]
            {
                CreateFirstAttackElementData(),

                // バフ解除（Friend）
                CreateBaseAttackElementData() with
                {
                    SortOrder = 2,
                    Target = AttackTarget.Friend,
                    EffectType = StateEffectType.RemoveBuff,
                    EffectiveCount = -1,
                    EffectValue = "All"
                },

                // デバフ解除（Friend）
                CreateBaseAttackElementData() with
                {
                    SortOrder = 3,
                    Target = AttackTarget.Friend,
                    EffectType = StateEffectType.RemoveDebuff,
                    EffectiveCount = -1,
                    EffectValue = "All"
                },

                // バフ解除（Foe）
                CreateBaseAttackElementData() with
                {
                    SortOrder = 4,
                    Target = AttackTarget.Foe,
                    EffectType = StateEffectType.RemoveBuff,
                    EffectiveCount = -1,
                    EffectValue = "All"
                },

                // デバフ解除（Foe）
                CreateBaseAttackElementData() with
                {
                    SortOrder = 5,
                    Target = AttackTarget.Foe,
                    EffectType = StateEffectType.RemoveDebuff,
                    EffectiveCount = -1,
                    EffectValue = "All"
                },
            };
        }
    }
}
#endif

