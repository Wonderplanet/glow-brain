using GLOW.Core.Domain.Repositories;
using GLOW.Modules.GameOption.Domain.ValueObjects;
using Zenject;

namespace GLOW.Modules.GameOption.Domain.UseCases
{
    public class SwitchTwoRowDeckGameOptionUseCase
    {
        [Inject] IUserPropertyRepository UserPropertyRepository { get; }
        
        public TwoRowDeckModeFlag SwitchTwoRowDeckGameOption()
        {
            var userPropertyModel = UserPropertyRepository.Get();
            var updatedUserPropertyModel = userPropertyModel with { IsTwoRowDeck = !userPropertyModel.IsTwoRowDeck };
            
            UserPropertyRepository.Save(updatedUserPropertyModel);
            
            return updatedUserPropertyModel.IsTwoRowDeck;
        }
    }
}