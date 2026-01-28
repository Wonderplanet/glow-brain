using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models.Stage;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Data.Translators
{
    public class StageContinueAdResultDataTranslator
    {
        // StageContinueAdResultData -> StageContinueAdResultModel
        public static StageContinueAdResultModel ToStageContinueAdResultModel(StageContinueAdResultData data)
        {
            return new StageContinueAdResultModel(
                new ContinueCount(data.ContinueCount),
                new ContinueCount(data.ContinueAdCount)
            );
        }
    }
}
