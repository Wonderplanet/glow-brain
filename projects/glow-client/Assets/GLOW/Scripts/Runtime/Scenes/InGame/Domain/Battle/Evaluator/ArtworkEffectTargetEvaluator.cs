using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.ArtworkEffect;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle.Evaluator
{
    public class ArtworkEffectTargetEvaluator : IArtworkEffectTargetEvaluator
    {
        // 効果対象かどうかを判定する
        public Dictionary<MasterDataId, ArtworkEffectTargetFlag> EvaluateTarget(
            IReadOnlyList<ArtworkEffectTargetRuleModel> targetRuleModels,
            IReadOnlyList<MstCharacterModel> unitModels,
            InGameRandomSeed randomSeed)
        {
            var result = new Dictionary<MasterDataId, ArtworkEffectTargetFlag>();

            // ルールまたはユニットが存在しない場合は全てのユニットを対象外にする
            if (targetRuleModels.Count == 0 || unitModels.Count == 0)
            {
                foreach (var unit in unitModels)
                {
                    result[unit.Id] = ArtworkEffectTargetFlag.False;
                }

                return result;
            }

            // スペシャルユニットは対象外とする
            var nonSpecialUnits = unitModels.Where(unit => unit.RoleType != CharacterUnitRoleType.Special).ToList();

            // TargetCountルールとそれ以外のルールを分離する
            var targetCountRule = targetRuleModels.FirstOrDefault(r => r.TargetType == ArtworkEffectTargetRuleType.TargetCount);
            var otherRules = targetRuleModels.Where(r => r.TargetType != ArtworkEffectTargetRuleType.TargetCount).ToList();

            var candidateUnits = FilterUnitsByRules(nonSpecialUnits, otherRules);

            // TargetCountルールがある場合はランダムに指定数のユニットを選択する
            if (targetCountRule != null && !targetCountRule.IsEmpty())
            {
                var targetCount = targetCountRule.EffectTargetValue.ToInt();
                var selectedUnits = SelectRandomUnits(candidateUnits, targetCount, randomSeed);

                var selectedUnitIds = new HashSet<MasterDataId>(selectedUnits.Select(u => u.Id));

                foreach (var unit in unitModels)
                {
                    result[unit.Id] = selectedUnitIds.Contains(unit.Id)
                        ? ArtworkEffectTargetFlag.True
                        : ArtworkEffectTargetFlag.False;
                }
            }
            else
            {
                var candidateUnitIds = new HashSet<MasterDataId>(candidateUnits.Select(u => u.Id));

                foreach (var unit in unitModels)
                {
                    result[unit.Id] = candidateUnitIds.Contains(unit.Id)
                        ? ArtworkEffectTargetFlag.True
                        : ArtworkEffectTargetFlag.False;
                }
            }

            return result;
        }

        // ルールに基づいてユニットをフィルタリングする
        IReadOnlyList<MstCharacterModel> FilterUnitsByRules(
            IReadOnlyList<MstCharacterModel> unitModels,
            IReadOnlyList<ArtworkEffectTargetRuleModel> rules)
        {
            var filteredUnits = unitModels.ToList();

            foreach (var rule in rules)
            {
                filteredUnits = filteredUnits.Where(unit => MatchesRule(unit, rule)).ToList();
            }

            return filteredUnits;
        }

        // ルールに合致するかを判定する
        bool MatchesRule(MstCharacterModel unit, ArtworkEffectTargetRuleModel rule)
        {
            switch (rule.TargetType)
            {
                case ArtworkEffectTargetRuleType.All:
                    return MatchesAllRule();

                case ArtworkEffectTargetRuleType.Unit:
                    return MatchesUnitRule(unit, rule.EffectTargetValue);

                case ArtworkEffectTargetRuleType.CharacterUnitRoleType:
                    return MatchesRoleTypeRule(unit, rule.EffectTargetValue);

                case ArtworkEffectTargetRuleType.CharacterColor:
                    return MatchesColorRule(unit, rule.EffectTargetValue);

                case ArtworkEffectTargetRuleType.Series:
                    return MatchesSeriesRule(unit, rule.EffectTargetValue);

                default:
                    return false;
            }
        }

        // 全てのユニットに合致するルールは常にtrueを返す
        bool MatchesAllRule()
        {
            return true;
        }

        // ユニットIDに合致するかを判定する
        bool MatchesUnitRule(MstCharacterModel unit, ArtworkEffectTargetValue targetValue)
        {
            var targetUnitId = targetValue.ToMasterDataId();
            return unit.Id == targetUnitId;
        }

        // ユニットのロールタイプに合致するかを判定する
        bool MatchesRoleTypeRule(MstCharacterModel unit, ArtworkEffectTargetValue targetValue)
        {
            var targetRoleType = targetValue.ToCharacterUnitRoleType();
            return unit.RoleType == targetRoleType;
        }

        // ユニットの属性に合致するかを判定する
        bool MatchesColorRule(MstCharacterModel unit, ArtworkEffectTargetValue targetValue)
        {
            var targetColor = targetValue.ToCharacterColor();
            return unit.Color == targetColor;
        }

        // ユニットのシリーズIDに合致するかを判定する
        bool MatchesSeriesRule(MstCharacterModel unit, ArtworkEffectTargetValue targetValue)
        {
            var targetSeriesId = targetValue.ToMasterDataId();
            return unit.MstSeriesId == targetSeriesId;
        }

        // 候補ユニットからランダムに指定数のユニットを選択する
        IReadOnlyList<MstCharacterModel> SelectRandomUnits(
            IReadOnlyList<MstCharacterModel> candidates,
            int count,
            InGameRandomSeed randomSeed)
        {
            if (candidates.Count <= count)
            {
                return candidates;
            }

            var selectedUnits = new List<MstCharacterModel>();
            var availableIndices = Enumerable.Range(0, candidates.Count).ToList();

            var random = new System.Random(randomSeed.Value);
            for (int i = 0; i < count && availableIndices.Count > 0; i++)
            {
                var randomIndex = random.Next(0, availableIndices.Count);
                var selectedIndex = availableIndices[randomIndex];
                selectedUnits.Add(candidates[selectedIndex]);
                availableIndices.RemoveAt(randomIndex);
            }

            return selectedUnits;
        }
    }
}
