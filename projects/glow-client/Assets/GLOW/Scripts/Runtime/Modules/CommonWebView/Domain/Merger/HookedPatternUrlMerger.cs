using System.Collections.Generic;
using System.Linq;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;

namespace GLOW.Modules.CommonWebView.Domain.Merger
{
    public static class HookedPatternUrlMerger
    {
        public static HookedPatternUrl MergeAsOrPattern(IReadOnlyList<HookedPatternUrl> patterns)
        {
            if (patterns.IsEmpty())
            {
                return HookedPatternUrl.Empty;
            }
            
            var validPatterns = patterns
                .Where(p => !p.IsEmpty())
                .Select(p => p.Value)
                .ToList();

            if (!validPatterns.Any())
            {
                return HookedPatternUrl.Empty;
            }

            if (patterns.Count == 1)
            {
                return new HookedPatternUrl(validPatterns.First());
            }
            
            var groupedPatterns = validPatterns.Select(p => "(?:" + p + ")").ToList();
            var mergedPattern = string.Join("|", groupedPatterns);
            
            return new HookedPatternUrl(mergedPattern);
        }
    }
}