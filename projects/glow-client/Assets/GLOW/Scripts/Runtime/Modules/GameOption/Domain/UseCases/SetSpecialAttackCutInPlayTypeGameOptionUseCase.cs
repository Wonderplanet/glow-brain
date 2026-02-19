using GLOW.Core.Domain.Repositories;
using GLOW.Modules.GameOption.Domain.Constants;
using Zenject;

namespace GLOW.Modules.GameOption.Domain.UseCases
{
    public class SetSpecialAttackCutInPlayTypeGameOptionUseCase
    {
        [Inject] IUserPropertyRepository UserPropertyRepository { get; }

        public SpecialAttackCutInPlayType SetSpecialAttackCutInPlayTypeGameOption(SpecialAttackCutInPlayType playType)
        {
            var userPropertyModel = UserPropertyRepository.Get();
            var updatedUserPropertyModel = userPropertyModel with { SpecialAttackCutInPlayType = playType };
            
            UserPropertyRepository.Save(updatedUserPropertyModel);
            
            return updatedUserPropertyModel.SpecialAttackCutInPlayType;
        }
    }
}