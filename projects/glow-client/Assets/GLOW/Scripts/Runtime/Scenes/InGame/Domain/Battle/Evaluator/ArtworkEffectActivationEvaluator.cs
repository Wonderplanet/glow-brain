using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.ArtworkEffect;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.Evaluator
{
    // 発動条件を満たしているかを判断する
    public class ArtworkEffectActivationEvaluator : IArtworkEffectActivationEvaluator
    {
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }

        public ArtworkEffectActivationFlag EvaluateActivation(
            IReadOnlyList<ArtworkEffectActivationRuleModel> activationRuleModels,
            IReadOnlyList<DeckUnitModel> unitModels)
        {
            // DeckUnitModelからMstCharacterModelを取得（IsEmptyの場合はスキップ）
            var mstUnitModels = unitModels
                .Where(unit => !unit.CharacterId.IsEmpty())
                .Select(unit => MstCharacterDataRepository.GetCharacter(unit.CharacterId))
                .ToList();

            return EvaluateActivation(activationRuleModels, mstUnitModels);
        }

        public ArtworkEffectActivationFlag EvaluateActivation(
            IReadOnlyList<ArtworkEffectActivationRuleModel> activationRuleModels,
            IReadOnlyList<MstCharacterModel> unitModels)
        {
            // 空のルールまたはNoneのみの場合は無条件でTrue
            if (activationRuleModels == null || activationRuleModels.Count == 0)
            {
                return ArtworkEffectActivationFlag.True;
            }

            if (activationRuleModels.All(rule => rule.ConditionType == ArtworkEffectActivationRuleType.None))
            {
                return ArtworkEffectActivationFlag.True;
            }

            // Countタイプの条件を取得
            var countRule = activationRuleModels
                .FirstOrDefault(
                    rule => rule.ConditionType == ArtworkEffectActivationRuleType.Count,
                    ArtworkEffectActivationRuleModel.Empty);

            // Count以外の条件を取得
            var otherRules = activationRuleModels
                .Where(rule => rule.ConditionType != ArtworkEffectActivationRuleType.Count &&
                               rule.ConditionType != ArtworkEffectActivationRuleType.None)
                .ToList();

            // Countのみの場合は、ユニット配列の要素数で判定
            if (!countRule.IsEmpty() && otherRules.Count == 0)
            {
                var requiredCount = countRule.EffectActivationValue.ToInt();
                return unitModels.Count >= requiredCount
                    ? ArtworkEffectActivationFlag.True
                    : ArtworkEffectActivationFlag.False;
            }

            // 他の条件がある場合、条件にマッチするユニットをフィルタリング
            var matchedUnits = unitModels.Where(unit => MatchesAllConditions(unit, otherRules)).ToList();

            // Countが指定されている場合は個数判定
            if (countRule.IsEmpty())
            {
                var requiredCount = countRule.EffectActivationValue.ToInt();
                return matchedUnits.Count >= requiredCount
                    ? ArtworkEffectActivationFlag.True
                    : ArtworkEffectActivationFlag.False;
            }

            // Countがない場合は、1体以上マッチすればTrue
            return matchedUnits.Count > 0
                ? ArtworkEffectActivationFlag.True
                : ArtworkEffectActivationFlag.False;
        }

        bool MatchesAllConditions(
            MstCharacterModel unit,
            IReadOnlyList<ArtworkEffectActivationRuleModel> rules)
        {
            // 全ての条件を満たす必要がある（AND条件）
            foreach (var rule in rules)
            {
                if (!MatchesCondition(unit, rule))
                {
                    return false;
                }
            }

            return true;
        }

        bool MatchesCondition(MstCharacterModel unit, ArtworkEffectActivationRuleModel rule)
        {
            switch (rule.ConditionType)
            {
                case ArtworkEffectActivationRuleType.None:
                    return true;

                case ArtworkEffectActivationRuleType.Unit:
                    return unit.Id == rule.EffectActivationValue.ToMasterDataId();

                case ArtworkEffectActivationRuleType.CharacterUnitRoleType:
                    return unit.RoleType == rule.EffectActivationValue.ToCharacterUnitRoleType();

                case ArtworkEffectActivationRuleType.CharacterColor:
                    return unit.Color == rule.EffectActivationValue.ToCharacterColor();

                case ArtworkEffectActivationRuleType.Series:
                    return unit.MstSeriesId == rule.EffectActivationValue.ToMasterDataId();

                default:
                    return false;
            }
        }
    }
}
