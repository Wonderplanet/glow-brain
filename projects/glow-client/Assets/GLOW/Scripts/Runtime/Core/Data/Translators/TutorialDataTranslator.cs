using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public static class TutorialDataTranslator
    {
        public static MstTutorialModel Translate(MstTutorialData data)
        {
            return new MstTutorialModel(
                new MasterDataId(data.Id),
                data.Type,
                new SortOrder(data.SortOrder),
                new TutorialFunctionName(data.FunctionName),
                data.ConditionType,
                new TutorialConditionValue(data.ConditionValue));
        }
    }
}
