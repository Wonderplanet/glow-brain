using System;
using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects.Quest;

namespace GLOW.Core.Domain.ValueObjects.Stage
{
    public record StageReleaseRequireSentence(string Value)
    {
        public static StageReleaseRequireSentence Empty { get; } = new(string.Empty);

        public static StageReleaseRequireSentence CreateReleaseRequiredSentence(int releaseRequiredStageNumber)
        {
            var format = "{0}話クリアで開放";
            return new StageReleaseRequireSentence(ZString.Format(format, releaseRequiredStageNumber));
        }

        public static StageReleaseRequireSentence CreateOpenLimitSentence(DateTimeOffset startAt)
        {
            var format = "{0}年\n{1}月{2}日\n開放！！";
            return new StageReleaseRequireSentence(ZString.Format(format, startAt.Year, startAt.Month, startAt.Day));
        }
    };
}
