#if GLOW_INGAME_DEBUG
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Debugs.InGame.Domain.Models;

namespace GLOW.Debugs.InGame.Domain.Constants.DebugSkillTemplates
{
    // バフ解除テンプレート
    public static class DebugRemoveBuffTemplates
    {
        public static IReadOnlyList<(string name, IReadOnlyList<DebugAttackElementData> elementData)> GetTemplates()
        {
            return new List<(string, IReadOnlyList<DebugAttackElementData>)>
            {
                ("敵_全解除", CreateRemoveAllBuff()),

                // QA要望テンプレート
                ("QA1_敵_1体_攻撃UPのみ", CreateQA1_Single_AttackUpOnly()),
                ("QA2_敵_被ダメカットのみ", CreateQA2_DamageCutOnly()),
                ("QA3_敵_黄_最大4個_全解除", CreateQA3_Yellow_Max4()),
                ("QA4_敵_テクニカル_最大5個_全解除", CreateQA4_Technical_Max5()),
                ("QA5_敵_1個_緑_最大3個_全解除", CreateQA5_Green_Condition()),
                ("QA6_敵_2個_サポート_全解除", CreateQA6_Support_Condition()),
                ("QA7_敵_1体_被ダメカットのみ_緑_最大3個", CreateQA7_Single_DamageCut_Green()),
                ("QA8_敵_固定値回復のみ_テクニカル_全解除", CreateQA8_Regeneration_Technical()),

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
                Target: AttackTarget.Foe,    // 対象
                TargetType: AttackTargetType.All,
                TargetColors: "All",
                TargetRoles: "All",
                TargetMstSeriesIds: string.Empty,
                TargetMstCharacterIds: string.Empty,
                RangeStartType: AttackRangePointType.Page,  // ページ全体
                RangeStartParameter: 0,
                RangeEndType: AttackRangePointType.Page,    // ページ全体
                RangeEndParameter: 0,
                MaxTargetCount: -1, // 体数制限
                DamageType: AttackDamageType.None,
                HitType: AttackHitType.Normal,
                HitParameter1: 0,
                HitParameter2: 0,
                HitEffectId: "dageki_1",
                IsHitStop: false,
                Probability: 100,
                PowerParameterType: AttackPowerParameterType.Percentage,
                PowerParameter: 0,
                EffectType: StateEffectType.None,   // 効果なし（対象検知用）
                EffectiveCount: -1,
                EffectiveDuration: 0,
                EffectParameter: 0,
                EffectValue: string.Empty,
                EffectTriggerRoles: string.Empty,
                EffectTriggerColors: string.Empty,
                AttackDelay: 0
            );
        }

        // ベーステンプレート（バフ解除用）
        static DebugAttackElementData CreateBaseAttackElementData()
        {
            return new DebugAttackElementData(
                Id: string.Empty,
                MstAttackId: string.Empty,
                SortOrder: 2,
                AttackType: AttackType.Direct,
                Target: AttackTarget.Foe,    // 対象
                TargetType: AttackTargetType.Character,
                TargetColors: "All",    // 属性
                TargetRoles: "All",     // ロール
                TargetMstSeriesIds: string.Empty,
                TargetMstCharacterIds: string.Empty,
                RangeStartType: AttackRangePointType.Page,  // ページ全体
                RangeStartParameter: 0,
                RangeEndType: AttackRangePointType.Page,    // ページ全体
                RangeEndParameter: 0,
                MaxTargetCount: -1, // 体数制限
                DamageType: AttackDamageType.None,
                HitType: AttackHitType.Normal,
                HitParameter1: 0,
                HitParameter2: 0,
                HitEffectId: "dageki_1",
                IsHitStop: false,
                Probability: 100,
                PowerParameterType: AttackPowerParameterType.Percentage,
                PowerParameter: 0,
                EffectType: StateEffectType.RemoveBuff,   // バフ解除
                EffectiveCount: -1,     // 解除数制限
                EffectiveDuration: 0,
                EffectParameter: 0,
                EffectValue: "All", // 解除するStateEffectType
                EffectTriggerRoles: string.Empty,
                EffectTriggerColors: string.Empty,
                AttackDelay: 0
            );
        }

        // 敵_全体_全解除
        static IReadOnlyList<DebugAttackElementData> CreateRemoveAllBuff()
        {
            return new[] { CreateFirstAttackElementData(), CreateBaseAttackElementData() };
        }

        // QA1: 敵_1体_攻撃UPのみ
        static IReadOnlyList<DebugAttackElementData> CreateQA1_Single_AttackUpOnly()
        {
            var element = CreateBaseAttackElementData() with
            {
                MaxTargetCount = 1,
                EffectValue = "[AttackPowerUp]"
            };

            return new[] { CreateFirstAttackElementData(), element };
        }

        // QA2: 敵_被ダメカットのみ
        static IReadOnlyList<DebugAttackElementData> CreateQA2_DamageCutOnly()
        {
            var element = CreateBaseAttackElementData() with
            {
                EffectValue = "[DamageCut]"
            };

            return new[] { CreateFirstAttackElementData(), element };
        }

        // QA3: 敵_黄_最大4個_全解除
        static IReadOnlyList<DebugAttackElementData> CreateQA3_Yellow_Max4()
        {
            var element = CreateBaseAttackElementData() with
            {
                TargetColors = "[Yellow]",
                EffectiveCount = 4
            };

            return new[] { CreateFirstAttackElementData(), element };
        }

        // QA4: 敵_テクニカル_最大5個_全解除
        static IReadOnlyList<DebugAttackElementData> CreateQA4_Technical_Max5()
        {
            var element = CreateBaseAttackElementData() with
            {
                TargetRoles = "[Technical]",
                EffectiveCount = 5
            };

            return new[] { CreateFirstAttackElementData(), element };
        }

        // QA5: 敵_1個_緑_最大3個_全解除 (Main+Sub)
        static IReadOnlyList<DebugAttackElementData> CreateQA5_Green_Condition()
        {
            var mainElement = CreateBaseAttackElementData() with
            {
                EffectiveCount = 1
            };

            var subElement = CreateBaseAttackElementData() with
            {
                SortOrder = 3,
                AttackType = AttackType.None,
                TargetColors = "[Green]",
                EffectiveCount = 3
            };

            return new[] { CreateFirstAttackElementData(), mainElement, subElement };
        }

        // QA6: 敵_2個_サポート_全解除 (Main+Sub)
        static IReadOnlyList<DebugAttackElementData> CreateQA6_Support_Condition()
        {
            var mainElement = CreateBaseAttackElementData() with
            {
                EffectiveCount = 2
            };

            var subElement = CreateBaseAttackElementData() with
            {
                SortOrder = 3,
                AttackType = AttackType.None,
                TargetRoles = "[Support]",
                EffectiveCount = -1
            };

            return new[] { CreateFirstAttackElementData(), mainElement, subElement };
        }

        // QA7: 敵_1体_被ダメカットのみ_緑_最大3個 (Main+Sub)
        static IReadOnlyList<DebugAttackElementData> CreateQA7_Single_DamageCut_Green()
        {
            var mainElement = CreateBaseAttackElementData() with
            {
                MaxTargetCount = 1,
                EffectValue = "[DamageCut]"
            };

            var subElement = CreateBaseAttackElementData() with
            {
                SortOrder = 3,
                AttackType = AttackType.None,
                MaxTargetCount = 1,
                TargetColors = "[Green]",
                EffectiveCount = 3
            };

            return new[] { CreateFirstAttackElementData(), mainElement, subElement };
        }

        // QA8: 敵_固定値回復のみ_テクニカル_全解除 (Main+Sub)
        static IReadOnlyList<DebugAttackElementData> CreateQA8_Regeneration_Technical()
        {
            var mainElement = CreateBaseAttackElementData() with
            {
                EffectValue = "[RegenerationByFixed]"
            };

            var subElement = CreateBaseAttackElementData() with
            {
                SortOrder = 3,
                AttackType = AttackType.None,
                TargetRoles = "[Technical]",
                EffectiveCount = -1
            };

            return new[] { CreateFirstAttackElementData(), mainElement, subElement };
        }

    }
}
#endif

