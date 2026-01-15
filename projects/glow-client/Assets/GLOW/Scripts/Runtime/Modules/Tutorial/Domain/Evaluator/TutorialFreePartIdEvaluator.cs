using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.Definitions;

namespace GLOW.Modules.Tutorial.Domain.Evaluator
{
    public class TutorialFreePartIdEvaluator
    {
        // チュートリアル追加する際、関係する部分メンテ箇所あれば更新すること
        public static ContentMaintenanceTarget[] GetContentMaintenanceTarget(TutorialFunctionName tutorialFunctionName)
        {
            if (tutorialFunctionName == TutorialFreePartIdDefinitions.ReleaseAdventBattle ||
                tutorialFunctionName == TutorialFreePartIdDefinitions.TransitAdventBattle ||
                tutorialFunctionName == TutorialFreePartIdDefinitions.TransitRaidAdventBattle)
            {
                return ContentMaintenanceTarget.AdventBattle;
            }

            if (tutorialFunctionName == TutorialFreePartIdDefinitions.ReleasePvp ||
                tutorialFunctionName == TutorialFreePartIdDefinitions.TransitPvp)
            {
                return ContentMaintenanceTarget.Pvp;
            }

            if (tutorialFunctionName == TutorialFreePartIdDefinitions.ReleaseEnhanceQuest ||
                tutorialFunctionName == TutorialFreePartIdDefinitions.TransitEnhanceQuest)
            {
                return ContentMaintenanceTarget.EnhanceQuest;
            }

            return new []{ContentMaintenanceTarget.Empty};
        }
    }
}
