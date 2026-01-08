using Cysharp.Threading.Tasks;
using GLOW.Scenes.Home.Domain.Misc;
using Zenject;

namespace GLOW.Scenes.OutpostEnhance.Domain.UseCases
{
    public class ApplyUpdatedOutpostArtworkUseCase
    {
        [Inject] IOutpostArtworkApplier OutpostArtworkApplier { get; }

        public void AsyncApply()
        {
            OutpostArtworkApplier.AsyncApplyOutpostArtwork();
        }

        public async UniTask Apply()
        {
            await OutpostArtworkApplier.ApplyOutpostArtwork(default);
        }
    }
}
