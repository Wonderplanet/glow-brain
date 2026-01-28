using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using WonderPlanet.UnityStandard.Extension;

namespace GLOW.Core.Domain.Evaluator
{
    public static class InGameSpecialRuleAchievingEvaluator
    {
        public static bool IsAchievedSpecialRule(
            MstCharacterModel mstCharacter,
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            // イベントが設定されていない場合は特別ルールを満たしているとする
            if (mstInGameSpecialRuleModels.Count <= 0)
            {
                return true;
            }

            // レアリティ
            var partyRarities = GetAndTranslateRarities(mstInGameSpecialRuleModels);
            bool isContainsRarity = partyRarities.IsEmpty() || partyRarities.Contains(mstCharacter.Rarity);

            // 作品
            var seriesIds = GetAndTranslateSeriesIds(mstInGameSpecialRuleModels);
            bool isContainsSeries = seriesIds.IsEmpty() || seriesIds.Contains(mstCharacter.MstSeriesId);

            // 攻撃範囲
            var attackRangeTypes = GetAndTranslateAttackRangeTypes(mstInGameSpecialRuleModels);
            bool isContainsAttackRange = attackRangeTypes.IsEmpty() || attackRangeTypes.Contains(mstCharacter.AttackRangeType);

            // ユニットロール
            var unitRoleTypes = GetAndTranslateUnitRoleTypes(mstInGameSpecialRuleModels);
            bool isContainsUnitRole = unitRoleTypes.IsEmpty() || unitRoleTypes.Contains(mstCharacter.RoleType);

            // 属性
            var unitColors = GetAndTranslateUnitColor(mstInGameSpecialRuleModels);
            bool isContainsColor = unitColors.IsEmpty() || unitColors.Contains(mstCharacter.Color);

            return isContainsRarity && isContainsSeries && isContainsAttackRange && isContainsUnitRole && isContainsColor;
        }

        static List<Rarity> GetAndTranslateRarities(IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            return mstInGameSpecialRuleModels
                .Where(mst => mst.RuleType == RuleType.PartyRarity)
                .Select(model => model.RuleValue.ToRarity())
                .ToList();
        }

        static List<MasterDataId> GetAndTranslateSeriesIds(IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            return mstInGameSpecialRuleModels
                .Where(mst => mst.RuleType == RuleType.PartySeries)
                .Select(model => model.RuleValue.ToSeriesId())
                .ToList();
        }

        static List<CharacterUnitRoleType> GetAndTranslateUnitRoleTypes(IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            return mstInGameSpecialRuleModels
                .Where(mst => mst.RuleType == RuleType.PartyRoleType)
                .Select(model => model.RuleValue.ToUnitRoleType())
                .ToList();
        }

        static List<CharacterColor> GetAndTranslateUnitColor(IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            return mstInGameSpecialRuleModels
                .Where(mst => mst.RuleType == RuleType.PartyColor)
                .Select(model => model.RuleValue.ToCharacterColor())
                .ToList();
        }

        static List<CharacterAttackRangeType> GetAndTranslateAttackRangeTypes(IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            return mstInGameSpecialRuleModels
                .Where(mst => mst.RuleType == RuleType.PartyAttackRangeType)
                .Select(model => model.RuleValue.ToAttackRangeType())
                .ToList();
        }

        public static InGameSpecialRuleAchievedFlag CreateAchievedSpecialRuleFlag(
            IReadOnlyList<MstCharacterModel> mstCharacterModels,
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels)
        {
            // 特別ルールがない＝達成している
            if (!mstInGameSpecialRuleModels.Any()) return InGameSpecialRuleAchievedFlag.True;

            bool isPartyNotAchieveInGameSpecialRule = false;
            foreach (var mstCharacter in mstCharacterModels)
            {
                if (!IsAchievedSpecialRule(mstCharacter, mstInGameSpecialRuleModels))
                {
                    isPartyNotAchieveInGameSpecialRule = true;
                    break;
                }
            }

            // パーティ内の全ユニットが特別ルールを満たしていない場合は達成していない
            return new InGameSpecialRuleAchievedFlag(!isPartyNotAchieveInGameSpecialRule);
        }
    }
}
