using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;

namespace GLOW.Core.Domain.Repositories
{
    public interface IUserPropertyRepository
    {
        UniTask Load(CancellationToken cancellationToken);
        public void Save(UserPropertyModel userPropertyModel);
        public UserPropertyModel Get();
    }
}
