using GLOW.Modules.Tutorial.Application.Context;
using GLOW.Modules.Tutorial.Presentation.Sequence;
using GLOW.Modules.Tutorial.Presentation.Sequence.FreePart;
using Zenject;

namespace GLOW.Modules.Tutorial.Application.Installers
{
    public class TutorialInstaller : Installer
    {
        public override void InstallBindings()
        {
            Container.BindInterfacesTo<TutorialContext>().AsCached();
            Container.BindInterfacesTo<FreePartTutorialContext>().AsCached();
            Container.BindInterfacesTo<PvpTopTutorialContext>().AsCached();

            Container.BindFactory<MainPart1TutorialSequence, PlaceholderFactory<MainPart1TutorialSequence>>().AsCached();
            Container.BindFactory<MainPart2TutorialSequence, PlaceholderFactory<MainPart2TutorialSequence>>().AsCached();
            Container.BindFactory<MainPart3TutorialSequence, PlaceholderFactory<MainPart3TutorialSequence>>().AsCached();
            
            Container.BindFactory<ReleaseHardTutorialSequence, 
                PlaceholderFactory<ReleaseHardTutorialSequence>>().AsCached();
            Container.BindFactory<ReleaseEventQuestTutorialSequence, 
                PlaceholderFactory<ReleaseEventQuestTutorialSequence>>().AsCached();
            Container.BindFactory<ReleasePvpTutorialSequence, 
                PlaceholderFactory<ReleasePvpTutorialSequence>>().AsCached();
            Container.BindFactory<ReleaseAdventBattleTutorialSequence, 
                PlaceholderFactory<ReleaseAdventBattleTutorialSequence>>().AsCached();
            Container.BindFactory<IdleIncentiveTutorialSequence, 
                PlaceholderFactory<IdleIncentiveTutorialSequence>>().AsCached();
            Container.BindFactory<SpecialRoleTutorialSequence, 
                PlaceholderFactory<SpecialRoleTutorialSequence>>().AsCached();
            Container.BindFactory<OutpostEnhanceTutorialSequence, 
                PlaceholderFactory<OutpostEnhanceTutorialSequence>>().AsCached();
            Container.BindFactory<ReleaseEnhanceQuestTutorialSequence, 
                PlaceholderFactory<ReleaseEnhanceQuestTutorialSequence>>().AsCached();
            
            Container.BindFactory<TransitPvpTutorialSequence, 
                PlaceholderFactory<TransitPvpTutorialSequence>>().AsCached();
            
        }
    }
}
