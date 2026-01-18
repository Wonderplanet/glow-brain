using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.TutorialTipDialog.Domain.ValueObject;

namespace GLOW.Core.Data.Translators
{
    public static class TutorialTipI18nDataTranslator
    {
        public static MstTutorialTipModel Translate(MstTutorialTipI18nData data)
        {
            return new MstTutorialTipModel(
                new MasterDataId(data.MstTutorialId),
                new SortOrder(data.SortOrder),
                new TutorialTipDialogTitle(data.Title),
                new TutorialTipAssetKey(data.AssetKey));
        }
    }
}
