using GLOW.Core.Data.Data;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Data.Translators
{
    public class UserTutorialDataTranslator
    {
        public static UserTutorialFreePartModel ToUserTutorialFreePartModel(UsrTutorialData usrTutorialData)
        {
            return new UserTutorialFreePartModel(new TutorialFunctionName(usrTutorialData.MstTutorialFunctionName));
        }
    }
}
