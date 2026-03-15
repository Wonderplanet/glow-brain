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
            Container.BindInterfacesTo<EventQuestTutorialContext>().AsCached();
            Container.BindInterfacesTo<ArtworkEffectTutorialContext>().AsCached();
            Container.BindInterfacesTo<ReleaseHardTutorialContext>().AsCached();

            // 導入・メインパート
            Container.BindFactory<MainPart1TutorialSequence, PlaceholderFactory<MainPart1TutorialSequence>>().AsCached();
          
            // フリーパート
            Container.BindFactory<ReleaseHardTutorialSequence,
                PlaceholderFactory<ReleaseHardTutorialSequence>>().AsCached();
            Container.BindFactory<ReleasePvpTutorialSequence,
                PlaceholderFactory<ReleasePvpTutorialSequence>>().AsCached();
            Container.BindFactory<IdleIncentiveTutorialSequence,
                PlaceholderFactory<IdleIncentiveTutorialSequence>>().AsCached();
            Container.BindFactory<SpecialRoleTutorialSequence,
                PlaceholderFactory<SpecialRoleTutorialSequence>>().AsCached();
            Container.BindFactory<OutpostEnhanceTutorialSequence,
                PlaceholderFactory<OutpostEnhanceTutorialSequence>>().AsCached();
            Container.BindFactory<ReleaseEnhanceQuestTutorialSequence,
                PlaceholderFactory<ReleaseEnhanceQuestTutorialSequence>>().AsCached();
            Container.BindFactory<ReleaseHomeCreateTutorialSequence,
                PlaceholderFactory<ReleaseHomeCreateTutorialSequence>>().AsCached();
            Container.BindFactory<ReleaseArtworkEffectTutorialSequence,
                PlaceholderFactory<ReleaseArtworkEffectTutorialSequence>>().AsCached();
            
            // 原画編成 初遷移時チュートリアル
            Container.BindFactory<TransitArtworkEffectTutorialSequence,
                PlaceholderFactory<TransitArtworkEffectTutorialSequence>>().AsCached();

            // ランクマッチ 初遷移時チュートリアル
            Container.BindFactory<TransitPvpTutorialSequence,
                PlaceholderFactory<TransitPvpTutorialSequence>>().AsCached();
            
            // いいジャン祭 初遷移時チュートリアル
            Container.BindFactory<ReleaseEventQuestTutorialSequence,
                PlaceholderFactory<ReleaseEventQuestTutorialSequence>>().AsCached();

        }
    }
}
