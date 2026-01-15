using GLOW.Core.Domain.Repositories;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using Zenject;

namespace GLOW.Modules.GameOption.Domain.UseCases
{
    public class SwitchPushOffGameOptionUseCase
    {
        [Inject] IUserPropertyRepository UserPropertyRepository { get; }

        public PushOffFlag SwitchPushGameOption()
        {
            var userPropertyModel = UserPropertyRepository.Get();
            var updatedUserPropertyModel = userPropertyModel with { IsPushOff = !userPropertyModel.IsPushOff };

            UserPropertyRepository.Save(updatedUserPropertyModel);
            
            return updatedUserPropertyModel.IsPushOff;
        }
    }
}