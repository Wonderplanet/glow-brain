using System;
using System.Collections.Generic;
using System.Text.RegularExpressions;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;

namespace GLOW.Core.Domain.Encoder
{
    public interface ISpecialAttackDescriptionEncoder
    {
        SpecialAttackInfoDescription DescriptionEncode(SpecialAttackInfoDescription baseDescription, IReadOnlyList<AttackElement> attackElements);
    }

    public class SpecialAttackDescriptionEncoder : ISpecialAttackDescriptionEncoder
    {
        struct ElementDictionaryKeys
        {
            public string ProvabilityKey;
            public string PowerParameterKey;
            public string EffectiveCountKey;
            public string EffectiveDurationKey;
            public string EffectParameterKey;
        }

        const string ProbabilityFormat = "{0}_probability";
        const string PowerParameterFormat = "{0}_power_parameter";
        const string EffectiveCountFormat = "{0}_effective_count";
        const string EffectiveDurationFormat = "{0}_effective_duration";
        const string EffectParameterFormat = "{0}_effect_parameter";

        public SpecialAttackInfoDescription DescriptionEncode(SpecialAttackInfoDescription baseDescription, IReadOnlyList<AttackElement> attackElements)
        {
            Dictionary<string, decimal> elementDictionary = new();

            foreach (var element in attackElements)
            {
                var elementKeys = GetElementDictionaryKeys(element.Id);

                elementDictionary.Add(elementKeys.ProvabilityKey, element.Probability.Value);
                elementDictionary.Add(elementKeys.PowerParameterKey, Convert.ToDecimal(element.PowerParameter.Value));
                elementDictionary.Add(elementKeys.EffectiveCountKey, element.StateEffect.EffectiveCount.Value);
                elementDictionary.Add(elementKeys.EffectiveDurationKey, (decimal)element.StateEffect.Duration.ToSeconds());
                elementDictionary.Add(elementKeys.EffectParameterKey, element.StateEffect.Parameter.Value);
                foreach (var subElement in element.SubElements)
                {
                    var subElementKeys = GetElementDictionaryKeys(subElement.Id);

                    elementDictionary.Add(subElementKeys.ProvabilityKey, subElement.Probability.Value);
                    elementDictionary.Add(subElementKeys.PowerParameterKey, Convert.ToDecimal(subElement.PowerParameter.Value));
                    elementDictionary.Add(subElementKeys.EffectiveCountKey, subElement.StateEffect.EffectiveCount.Value);
                    elementDictionary.Add(subElementKeys.EffectiveDurationKey, (decimal)subElement.StateEffect.Duration.ToSeconds());
                    elementDictionary.Add(subElementKeys.EffectParameterKey, subElement.StateEffect.Parameter.Value);
                }
            }

            return new SpecialAttackInfoDescription(ProcessString(baseDescription.Value, elementDictionary));
        }

        ElementDictionaryKeys GetElementDictionaryKeys(MasterDataId elementId)
        {
            return new ElementDictionaryKeys
            {
                ProvabilityKey = string.Format(ProbabilityFormat, elementId),
                PowerParameterKey = string.Format(PowerParameterFormat, elementId),
                EffectiveCountKey = string.Format(EffectiveCountFormat, elementId),
                EffectiveDurationKey = string.Format(EffectiveDurationFormat, elementId),
                EffectParameterKey = string.Format(EffectParameterFormat, elementId)
            };
        }

        string ProcessString(string baseDescription, Dictionary<string, decimal> elementDictionary)
        {
            // {} で囲まれた部分と、その直後の文字を正規表現で抽出
            var regex = new Regex(@"{(.*?)}(.)");
            var matches = regex.Matches(baseDescription);

            foreach (Match match in matches)
            {
                string expression = match.Groups[1].Value;
                string nextChar = match.Groups[2].Value;
                decimal result = 0;

                // もし式に加算(+)が含まれている場合
                if (expression.Contains("+"))
                {
                    var keys = expression.Split('+');
                    foreach (var key in keys)
                    {
                        if (elementDictionary.TryGetValue(key, out var value))
                        {
                            result += value;
                        }
                    }
                }
                else
                {
                    // 単一のキーが入っている場合、そのまま値を代入
                    if (elementDictionary.TryGetValue(expression, out var value))
                    {
                        result = value;
                    }
                }

                // 次の文字が%または秒の場合は "0.##" 形式、それ以外は四捨五入
                string formattedResult = nextChar == "%" || nextChar == "秒"
                    ? result.ToString("0.##")
                    : Math.Round(result).ToString("0");

                // 演算した結果を元のテンプレートの {xxx} に置き換える
                baseDescription = baseDescription.Replace(match.Value, formattedResult + nextChar);
            }

            return baseDescription;
        }
    }
}
