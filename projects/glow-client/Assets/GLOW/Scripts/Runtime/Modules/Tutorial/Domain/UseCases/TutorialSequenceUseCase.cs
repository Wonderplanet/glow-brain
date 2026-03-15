using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Model;
using GLOW.Modules.Tutorial.Domain.Repository;
using GLOW.Modules.Tutorial.Domain.ValueObject;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class TutorialSequenceUseCase
    {
        [Inject] ITutorialSequenceRepository TutorialSequenceRepository { get; }

        public async  UniTask<TutorialSequenceUseCaseModel> GetTutorialSequenceUseCaseModel(TutorialSequenceAssetKey assetKey, CancellationToken cancellationToken)
        {
            var assetPath = TutorialSequenceAssetPath.ToTutorialSequenceAssetPath(assetKey);
            var model = await TutorialSequenceRepository.LoadTutorialSequence(assetPath, cancellationToken);

            return new TutorialSequenceUseCaseModel(model);
        }
    }
}
