using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;

namespace GLOW.Core.Data.Translators
{
    public class MstResultTipsI18nDataTranslator
    {
        public static MstResultTipsModel Translate(MstResultTipI18nData data)
        {
            return new MstResultTipsModel(
                new UserLevel(data.UserLevel),
                new StageResultTips(data.ResultTips)
            );
        }
    }
}
