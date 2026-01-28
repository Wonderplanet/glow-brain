using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.TutorialTipDialog.Domain.ValueObject;

namespace GLOW.Core.Domain.Models
{
    public record MstTutorialTipModel(
        MasterDataId TutorialTipId,
        SortOrder SortOrder,
        TutorialTipDialogTitle TutorialTipDialogTitle,
        TutorialTipAssetKey TutorialTipAssetKey)
    {
        public static MstTutorialTipModel Empty { get; } = new(
            MasterDataId.Empty,
            SortOrder.Empty,
            TutorialTipDialogTitle.Empty,
            TutorialTipAssetKey.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
