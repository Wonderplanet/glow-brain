using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public record MstTutorialModel(
        MasterDataId TutorialId,
        TutorialType TutorialType,
        SortOrder SortOrder,
        TutorialFunctionName TutorialFunctionName,
        TutorialConditionType ConditionType,
        TutorialConditionValue ConditionValue)
    {
        public static MstTutorialModel Empty { get; } = new MstTutorialModel(
            MasterDataId.Empty,
            TutorialType.Free,
            SortOrder.Empty,
            TutorialFunctionName.Empty,
            TutorialConditionType.UserLevel,
            TutorialConditionValue.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
