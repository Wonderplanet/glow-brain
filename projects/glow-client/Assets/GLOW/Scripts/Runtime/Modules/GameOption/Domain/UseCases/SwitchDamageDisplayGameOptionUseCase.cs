using GLOW.Core.Domain.Repositories;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using Zenject;

namespace GLOW.Modules.GameOption.Domain.UseCases
{
    public class SwitchDamageDisplayGameOptionUseCase
    {
        [Inject] IUserPropertyRepository UserPropertyRepository { get; }

        public DamageDisplayFlag SwitchDamageDisplayGameOption()
        {
            var userPropertyModel = UserPropertyRepository.Get();
            var updatedUserPropertyModel = userPropertyModel with { IsDamageDisplay = !userPropertyModel.IsDamageDisplay };

            UserPropertyRepository.Save(updatedUserPropertyModel);

            return updatedUserPropertyModel.IsDamageDisplay;
        }
    }
}

