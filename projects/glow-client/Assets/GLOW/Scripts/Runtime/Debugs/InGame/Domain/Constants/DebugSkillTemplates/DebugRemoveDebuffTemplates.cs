#if GLOW_INGAME_DEBUG
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Debugs.InGame.Domain.Models;

namespace GLOW.Debugs.InGame.Domain.Constants.DebugSkillTemplates
{
    // デバフ解除テンプレート
    public static class DebugRemoveDebuffTemplates
    {
        public static IReadOnlyList<(string name, IReadOnlyList<DebugAttackElementData> elementData)> GetTemplates()
        {
            return new List<(string, IReadOnlyList<DebugAttackElementData>)>
            {
                ("味方_全解除", CreateRemoveAllDebuff()),

                // QA要望テンプレート
                ("QA1_味方_1体_毒のみ", CreateQA1_Single_PoisonOnly()),
                ("QA2_味方_火傷のみ", CreateQA2_BurnOnly()),
                ("QA3_味方_青_最大1個_全解除", CreateQA3_Blue_Max1()),
                ("QA4_味方_テクニカル_最大2個_全解除", CreateQA4_Technical_Max2()),
                ("QA5_味方_3個_黄_最大4個_全解除", CreateQA5_Yellow_Condition()),
                ("QA6_味方_5個_サポート_全解除", CreateQA6_Support_Condition()),
                ("QA7_自身_攻撃DOWNのみ", CreateQA7_Self_AttackDownOnly()),
                ("QA8_味方と自身_攻撃DOWNのみ", CreateQA8_Friend_AttackDownOnly()),
                ("QA9_味方_1体_攻撃DOWNのみ_緑_全解除", CreateQA9_Single_AttackDown_Green()),
                ("QA10_味方_弱体化のみ_アタック_最大4個", CreateQA10_Weakening_Attack()),
                ("QA11_自身_弱体化のみ+味方_1体_赤_全解除", CreateQA11_Self_Weakening_Friend_Red()),
                ("QA12_自身_移動速度DOWNのみ+味方_サポート_最大5個", CreateQA12_Self_MoveDown_Friend_Support()),
                ("QA13_自身_毒のみ+味方_1体_火傷のみ+敵_攻撃UPのみ", CreateQA13_Self_Friend_Foe_Single()),
                ("QA14_自身_最大2個+味方_最大1個+敵_1体_全解除", CreateQA14_Self_Friend_Foe_Multi()),
                ("QA15_自身_全解除+味方_無_火傷氷結のみ+敵_ディフェンス_最大6個", CreateQA15_Self_Friend_None_Foe_Defense()),
                ("QA16_自身_最大2個+味方_1体_最大2個+敵_1体_アタック_被ダメカットのみ", CreateQA16_Self_Friend_Foe_Attack()),

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
                RangeStartType: AttackRangePointType.Page,
                RangeStartParameter: 0,
                RangeEndType: AttackRangePointType.Page,
                RangeEndParameter: 0,
                MaxTargetCount: -1,
                DamageType: AttackDamageType.None,
                HitType: AttackHitType.Normal,
                HitParameter1: 0,
                HitParameter2: 0,
                HitEffectId: "dageki_1",
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

        // ベーステンプレート（デバフ解除用）
        static DebugAttackElementData CreateBaseAttackElementData()
        {
            return new DebugAttackElementData(
                Id: string.Empty,
                MstAttackId: string.Empty,
                SortOrder: 2,
                AttackType: AttackType.Direct,
                Target: AttackTarget.FriendOnly,
                TargetType: AttackTargetType.Character,
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
                HitEffectId: "dageki_1",
                IsHitStop: false,
                Probability: 100,
                PowerParameterType: AttackPowerParameterType.Percentage,
                PowerParameter: 0,
                EffectType: StateEffectType.RemoveDebuff,   // デバフ解除
                EffectiveCount: -1,
                EffectiveDuration: 0,
                EffectParameter: 0,
                EffectValue: "All",
                EffectTriggerRoles: string.Empty,
                EffectTriggerColors: string.Empty,
                AttackDelay: 0
            );
        }

        // 味方_全体_全解除
        static IReadOnlyList<DebugAttackElementData> CreateRemoveAllDebuff()
        {
            return new[] { CreateFirstAttackElementData(), CreateBaseAttackElementData() };
        }

        // QA1: 味方_1体_毒のみ
        static IReadOnlyList<DebugAttackElementData> CreateQA1_Single_PoisonOnly()
        {
            var element = CreateBaseAttackElementData() with
            {
                MaxTargetCount = 1,
                EffectValue = "[Poison]"
            };

            return new[] { CreateFirstAttackElementData(), element };
        }

        // QA2: 味方_火傷のみ
        static IReadOnlyList<DebugAttackElementData> CreateQA2_BurnOnly()
        {
            var element = CreateBaseAttackElementData() with
            {
                EffectValue = "[Burn]"
            };

            return new[] { CreateFirstAttackElementData(), element };
        }

        // QA3: 味方_青_最大1個_全解除
        static IReadOnlyList<DebugAttackElementData> CreateQA3_Blue_Max1()
        {
            var element = CreateBaseAttackElementData() with
            {
                TargetColors = "[Blue]",
                EffectiveCount = 1
            };

            return new[] { CreateFirstAttackElementData(), element };
        }

        // QA4: 味方_テクニカル_最大2個_全解除
        static IReadOnlyList<DebugAttackElementData> CreateQA4_Technical_Max2()
        {
            var element = CreateBaseAttackElementData() with
            {
                TargetRoles = "[Technical]",
                EffectiveCount = 2
            };

            return new[] { CreateFirstAttackElementData(), element };
        }

        // QA5: 味方_3個_黄_最大4個_全解除 (Main+Sub)
        static IReadOnlyList<DebugAttackElementData> CreateQA5_Yellow_Condition()
        {
            var mainElement = CreateBaseAttackElementData() with
            {
                EffectiveCount = 3
            };

            var subElement = CreateBaseAttackElementData() with
            {
                SortOrder = 3,
                AttackType = AttackType.None,
                TargetColors = "[Yellow]",
                EffectiveCount = 4
            };

            return new[] { CreateFirstAttackElementData(), mainElement, subElement };
        }

        // QA6: 味方_5個_サポート_全解除 (Main+Sub)
        static IReadOnlyList<DebugAttackElementData> CreateQA6_Support_Condition()
        {
            var mainElement = CreateBaseAttackElementData() with
            {
                EffectiveCount = 5
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

        // QA7: 自身_攻撃DOWNのみ
        static IReadOnlyList<DebugAttackElementData> CreateQA7_Self_AttackDownOnly()
        {
            var element = CreateBaseAttackElementData() with
            {
                Target = AttackTarget.Self,
                MaxTargetCount = 1,
                EffectValue = "[AttackPowerDown]"
            };

            return new[] { CreateFirstAttackElementData(), element };
        }

        // QA8: 味方と自身_攻撃DOWNのみ
        static IReadOnlyList<DebugAttackElementData> CreateQA8_Friend_AttackDownOnly()
        {
            var element = CreateBaseAttackElementData() with
            {
                Target = AttackTarget.Friend,
                EffectValue = "[AttackPowerDown]"
            };

            return new[] { CreateFirstAttackElementData(), element };
        }

        // QA9: 味方_1体_攻撃DOWNのみ_緑_全解除 (Main+Sub)
        static IReadOnlyList<DebugAttackElementData> CreateQA9_Single_AttackDown_Green()
        {
            var mainElement = CreateBaseAttackElementData() with
            {
                MaxTargetCount = 1,
                EffectValue = "[AttackPowerDown]"
            };

            var subElement = CreateBaseAttackElementData() with
            {
                SortOrder = 3,
                AttackType = AttackType.None,
                MaxTargetCount = 1,
                TargetColors = "[Green]",
                EffectiveCount = -1
            };

            return new[] { CreateFirstAttackElementData(), mainElement, subElement };
        }

        // QA10: 味方_弱体化のみ_アタック_最大4個 (Main+Sub)
        static IReadOnlyList<DebugAttackElementData> CreateQA10_Weakening_Attack()
        {
            var mainElement = CreateBaseAttackElementData() with
            {
                EffectValue = "[Weakening]"
            };

            var subElement = CreateBaseAttackElementData() with
            {
                SortOrder = 3,
                AttackType = AttackType.None,
                TargetRoles = "[Attack]",
                EffectiveCount = 4
            };

            return new[] { CreateFirstAttackElementData(), mainElement, subElement };
        }

        // QA11: 自身_弱体化のみ+味方_1体_赤_全解除 (2Element)
        static IReadOnlyList<DebugAttackElementData> CreateQA11_Self_Weakening_Friend_Red()
        {
            // Element1: 自身の弱体化のみ解除
            var selfElement = CreateBaseAttackElementData() with
            {
                Target = AttackTarget.Self,
                MaxTargetCount = 1,
                EffectValue = "[Weakening]"
            };

            // Element2 単体味方の赤のみ全解除
            var friendElement = CreateBaseAttackElementData() with
            {
                SortOrder = 4,
                MaxTargetCount = 1,
                TargetColors = "[Red]",
                EffectiveCount = -1
            };


            return new[] { CreateFirstAttackElementData(), selfElement, friendElement };
        }

        // QA12: 自身_移動速度DOWNのみ+味方_サポート_最大5個 (2Element)
        static IReadOnlyList<DebugAttackElementData> CreateQA12_Self_MoveDown_Friend_Support()
        {
            // Element1: 自身の移動速度DOWNのみ解除
            var selfElement = CreateBaseAttackElementData() with
            {
                Target = AttackTarget.Self,
                MaxTargetCount = 1,
                EffectValue = "[MoveSpeedDown]"
            };

            // Element2: 複数味方のサポートロールのみ最大5個
            var friendElement = CreateBaseAttackElementData() with
            {
                SortOrder = 3,
                TargetRoles = "[Support]",
                EffectiveCount = 5
            };

            return new[] { CreateFirstAttackElementData(), selfElement, friendElement };
        }

        // QA13: 自身_毒のみ+味方_1体_火傷のみ+敵_攻撃UPのみ (3Element)
        static IReadOnlyList<DebugAttackElementData> CreateQA13_Self_Friend_Foe_Single()
        {
            // Element1: 自身の毒のみ
            var selfElement = CreateBaseAttackElementData() with
            {
                Target = AttackTarget.Self,
                MaxTargetCount = 1,
                EffectValue = "[Poison]"
            };

            // Element2: 味方単体の火傷のみ
            var friendElement = CreateBaseAttackElementData() with
            {
                SortOrder = 3,
                MaxTargetCount = 1,
                EffectValue = "[Burn]"
            };

            // Element3: 相手複数の攻撃力UPのみ（バフ解除）
            var foeElement = CreateBaseAttackElementData() with
            {
                SortOrder = 4,
                Target = AttackTarget.Foe,
                EffectType = StateEffectType.RemoveBuff,
                EffectValue = "[AttackPowerUp]"
            };

            return new[] { CreateFirstAttackElementData(), selfElement, friendElement, foeElement };
        }

        // QA14: 自身_最大2個+味方_最大1個+敵_1体_全解除 (3Element)
        static IReadOnlyList<DebugAttackElementData> CreateQA14_Self_Friend_Foe_Multi()
        {
            // Element1: 自身デバフ最大2個
            var selfElement = CreateBaseAttackElementData() with
            {
                Target = AttackTarget.Self,
                MaxTargetCount = 1,
                EffectiveCount = 2
            };

            // Element2: 味方複数デバフ最大1個
            var friendElement = CreateBaseAttackElementData() with
            {
                SortOrder = 3,
                EffectiveCount = 1
            };

            // Element3: 相手単体バフ全て
            var foeElement = CreateBaseAttackElementData() with
            {
                SortOrder = 4,
                Target = AttackTarget.Foe,
                MaxTargetCount = 1,
                EffectType = StateEffectType.RemoveBuff,
                EffectiveCount = -1
            };

            return new[] { CreateFirstAttackElementData(), selfElement, friendElement, foeElement };
        }

        // QA15: 自身_全解除+味方_無_火傷氷結のみ+敵_ディフェンス_最大6個 (3Element)
        // 1：自身のデバフを全て解除
        // 2：味方が「無」属性の場合は「火傷」と「氷結」のみ解除
        // 3：相手が「ディフェンス」ロールの場合はバフを最大「6」個解除
        static IReadOnlyList<DebugAttackElementData> CreateQA15_Self_Friend_None_Foe_Defense()
        {
            // Element1: 自身のデバフを全て解除
            var selfElement = CreateBaseAttackElementData() with
            {
                Target = AttackTarget.Self,
                MaxTargetCount = 1,
            };

            // Element2: 味方が「無」属性の場合は「火傷」と「氷結」のみ解除（体数無制限）
            var friendElement = CreateBaseAttackElementData() with
            {
                SortOrder = 3,
                TargetColors = "[Colorless]",
                EffectValue = "[Burn,Freeze]"
            };

            // Element3: 相手が「ディフェンス」ロールの場合はバフを最大「6」個解除（体数無制限）
            var foeElement = CreateBaseAttackElementData() with
            {
                SortOrder = 4,
                Target = AttackTarget.Foe,
                TargetRoles = "[Defense]",
                EffectType = StateEffectType.RemoveBuff,
                EffectiveCount = 6
            };

            return new[] { CreateFirstAttackElementData(), selfElement, friendElement, foeElement };
        }

        // QA16: 自身_最大2個+味方_1体_最大2個+敵_1体_アタック_被ダメカットのみ (3Element)
        // 1：自身のデバフを「2」個解除
        // 2：味方「単体」のデバフを「2」個解除
        // 3：相手「単体」で「アタック」ロールの場合は「被ダメージカット」のみを解除
        static IReadOnlyList<DebugAttackElementData> CreateQA16_Self_Friend_Foe_Attack()
        {
            // Element1: 自身のデバフを「2」個解除
            var selfElement = CreateBaseAttackElementData() with
            {
                Target = AttackTarget.Self,
                MaxTargetCount = 1,
                EffectiveCount = 2
            };

            // Element2: 味方「単体」のデバフを「2」個解除
            var friendElement = CreateBaseAttackElementData() with
            {
                SortOrder = 3,
                MaxTargetCount = 1,
                EffectiveCount = 2
            };

            // Element3: 相手「単体」で「アタック」ロールの場合は「被ダメージカット」のみを解除
            var foeElement = CreateBaseAttackElementData() with
            {
                SortOrder = 4,
                Target = AttackTarget.Foe,
                MaxTargetCount = 1,
                TargetRoles = "[Attack]",
                EffectType = StateEffectType.RemoveBuff,
                EffectValue = "[DamageCut]"
            };

            return new[] { CreateFirstAttackElementData(), selfElement, friendElement, foeElement };
        }

    }
}
#endif

