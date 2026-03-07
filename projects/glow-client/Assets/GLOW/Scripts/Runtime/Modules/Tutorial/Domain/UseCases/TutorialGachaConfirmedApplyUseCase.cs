using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Modules.Tutorial.Domain.Applier;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class TutorialGachaConfirmedApplyUseCase
    {
        [Inject] ITutorialGachaConfirmedApplier TutorialGachaConfirmedApplier { get; }
        
        public async UniTask UpdateGachaConfirmedApplyIfNeeds(CancellationToken cancellationToken)
        {
            await TutorialGachaConfirmedApplier.ApplyPartyAndAvatarIfNeeds(cancellationToken);
        }
    }
}