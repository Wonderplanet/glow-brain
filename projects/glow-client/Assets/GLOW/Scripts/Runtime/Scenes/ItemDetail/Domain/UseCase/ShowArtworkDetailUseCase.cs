using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.ItemDetail.Domain.UseCase
{
    public class ShowArtworkDetailUseCase
    {
        [Inject] IMstArtworkFragmentDataRepository MstArtworkFragmentDataRepository { get; }

        public MasterDataId GetArtworkIdOfArtworkFragment(MasterDataId mstArtworkFragmentId)
        {
            var artworkFragment = MstArtworkFragmentDataRepository.GetArtworkFragment(mstArtworkFragmentId);

            return artworkFragment.MstArtworkId;
        }
    }
}
