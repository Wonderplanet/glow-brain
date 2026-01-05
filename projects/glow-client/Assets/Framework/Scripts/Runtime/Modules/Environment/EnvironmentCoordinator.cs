using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using UnityEngine.Scripting;

namespace WPFramework.Modules.Environment
{
    public sealed class EnvironmentCoordinator
    {
        readonly IEnvironmentContainer _container;

        public EnvironmentCoordinator(IEnvironmentContainer container)
        {
            _container = container;
        }

        [Preserve]
        public EnvironmentCoordinator() : this(new EnvironmentContainer())
        {
        }

        public async UniTask Fetch(CancellationToken cancellationToken, IEnvironmentProtocol[] protocols)
        {
            try
            {
                var listData = await EnvironmentProtocolExecutor.ExecuteProtocols(cancellationToken, protocols);
                _container.Save(listData);
            }
            finally
            {
                foreach (var protocol in protocols)
                {
                    protocol.Dispose();
                }
            }
        }

        public EnvironmentListData GetEnvironmentList()
        {
            return _container.Get();
        }

        public EnvironmentData FindConnectionEnvironment()
        {
            // NOTE: 接続環境は最初の要素を返す
            var data = _container?.Get()?.Environments?.FirstOrDefault();
            if (data == null)
            {
                throw new EnvironmentNotFoundException("Environment is not found.");
            }

            return data;
        }

        public bool ChangeConnectionEnvironment(string environmentName)
        {
            var environmentList = GetEnvironmentList();
            var environments = environmentList.Environments;
            var environment = environments.FirstOrDefault(env => env.Env == environmentName);
            if (environment == null)
            {
                return false;
            }

            // NOTE: 環境選択処理は配列の最初の環境を活かすため、選択した環境を配列の最初に持ってくる
            var fromIndex = environments.ToList().IndexOf(environment);
            var toIndex = 0;
            (environments[toIndex], environments[fromIndex]) = (environments[fromIndex], environments[toIndex]);
            _container.Save(new EnvironmentListData(environments));
            return true;
        }
    }
}
